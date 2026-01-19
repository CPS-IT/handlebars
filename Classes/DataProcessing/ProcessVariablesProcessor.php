<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace CPSIT\Typo3Handlebars\DataProcessing;

use CPSIT\Typo3Handlebars\Exception;
use CPSIT\Typo3Handlebars\Renderer;
use Psr\Log;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Core;
use TYPO3\CMS\Frontend;

/**
 * Data processor to process given variables, especially useful in combination with other data processors.
 *
 * Example (standalone):
 * =====================
 *
 * hero = HANDLEBARSTEMPLATE
 * hero {
 *   templateName = @hero
 *
 *   # ...
 *
 *   dataProcessing {
 *     10 = process-variables
 *     10 {
 *       if.isTrue.field = title
 *
 *       variables {
 *         header = TEXT
 *         header.field = title
 *
 *         teaser = TEXT
 *         teaser.field = teaser
 *         teaser.parseFunc < lib.parseFunc_RTE
 *
 *         # ...
 *       }
 *     }
 *   }
 * }
 *
 * Example (with other data processor):
 * ====================================
 *
 * accordion = HANDLEBARSTEMPLATE
 * accordion {
 *   templateName = @accordion
 *
 *   # ...
 *
 *   dataProcessing {
 *     10 = database-query
 *     10 {
 *       # ...
 *
 *       dataProcessing {
 *         10 = process-variables
 *         10 {
 *           table = tx_mysitepackage_domain_model_accordion_element
 *           as = accordionItem
 *           variables {
 *             template = @accordion-item
 *
 *             header = TEXT
 *             header.field = title
 *
 *             bodytext = TEXT
 *             bodytext.field = bodytext
 *             bodytext.parseFunc < lib.parseFunc_RTE
 *
 *             # ...
 *           }
 *         }
 *       }
 *     }
 *   }
 * }
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[DependencyInjection\Attribute\AutoconfigureTag('data.processor', ['identifier' => 'process-variables'])]
final readonly class ProcessVariablesProcessor implements Frontend\ContentObject\DataProcessorInterface
{
    public function __construct(
        private DataSource\DataSourceProvider $dataSourceProvider,
        private Log\LoggerInterface $logger,
    ) {}

    /**
     * @param array<string, mixed> $contentObjectConfiguration
     * @param array<string, mixed> $processorConfiguration
     * @param array<string|int, mixed> $processedData
     * @return array<string|int, mixed>
     * @throws Frontend\ContentObject\Exception\ContentRenderingException
     * @throws Exception\ConfiguredProcessorIsUnsupported
     * @throws Exception\ReservedVariableCannotBeUsed
     */
    public function process(
        Frontend\ContentObject\ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData,
    ): array {
        $data = null;

        $collection = new DataSource\DataSourceCollection();
        $collection->set(DataSource\DataSource::ContentObjectRenderer, $cObj->data);
        $collection->set(DataSource\DataSource::ContentObjectConfiguration, $contentObjectConfiguration);
        $collection->set(DataSource\DataSource::ProcessedData, $processedData);
        $collection->set(DataSource\DataSource::ProcessorConfiguration, $processorConfiguration);

        $variables = $collection->resolve('variables.', DataSource\DataSource::ProcessorConfiguration);

        // Early return if no or invalid variables to process are configured
        if (!\is_array($variables)) {
            return $processedData;
        }

        // Trigger pre-processors
        $variables = $this->triggerDataSourceAwareProcessors(
            $processorConfiguration,
            'preProcessing',
            $variables,
            $collection,
            $cObj,
        );

        try {
            $data = $this->dataSourceProvider->provide($collection);
        } catch (Exception\DataSourceIsMissingInCollection $exception) {
            $this->logger->warning(
                'No data provided for data source "{source}" while processing {table}:{uid}.',
                [
                    'source' => $exception->dataSource->value,
                    'table' => $cObj->getCurrentTable(),
                    'uid' => $collection->resolve('uid', DataSource\DataSource::ContentObjectRenderer, '*unknown*'),
                ],
            );
        } catch (Exception\DataSourceIsNotSupported $exception) {
            $this->logger->warning(
                'Invalid data source keyword "{source}" passed while processing {table}:{uid}.',
                [
                    'source' => $exception->dataSourceIdentifier,
                    'table' => $cObj->getCurrentTable(),
                    'uid' => $collection->resolve('uid', DataSource\DataSource::ContentObjectRenderer, '*unknown*'),
                ],
            );
        } catch (Exception\PathIsMissingInDataSource $exception) {
            $this->logger->warning(
                'Invalid path "{path}" for data source "{source}" passed while processing {table}:{uid}.',
                [
                    'path' => $exception->path,
                    'source' => $exception->dataSource->value,
                    'table' => $cObj->getCurrentTable(),
                    'uid' => $collection->resolve('uid', DataSource\DataSource::ContentObjectRenderer, '*unknown*'),
                ],
            );
        }

        $data ??= $cObj->data;
        $table = $collection->resolve(
            'table',
            [DataSource\DataSource::ProcessorConfiguration, DataSource\DataSource::ProcessedData],
            $cObj->getCurrentTable(),
        );
        $as = $collection->resolve('as', DataSource\DataSource::ProcessorConfiguration);

        // Use temporary cObj for processing
        $cObj = clone $cObj;
        $cObj->start($data, $table);

        // Early return if processing should be skipped according to a configured condition
        if (\is_array($processorConfiguration['if.'] ?? null) && !$cObj->checkIf($processorConfiguration['if.'])) {
            return $processedData;
        }

        // Process variables with temporary cObj
        $processor = Renderer\Variables\VariablesProcessor::for($cObj);
        $processedVariables = $processor->process($variables);

        // Trigger post-processors
        $processedVariables = $this->triggerDataSourceAwareProcessors(
            $processorConfiguration,
            'postProcessing',
            $processedVariables,
            $collection,
            $cObj,
        );

        // Apply processed variables, either override processed data (if no target variable name is given)
        // or merge with processed data using given target variable name ("as")
        if ($as === null) {
            $processedData = $processedVariables;
        } else {
            $processedData[$as] = $processedVariables;
        }

        return $processedData;
    }

    /**
     * @param array<string, mixed> $processorConfiguration
     * @param array<string|int, mixed> $variables
     * @return array<string|int, mixed>
     * @throws Exception\ConfiguredProcessorIsUnsupported
     */
    private function triggerDataSourceAwareProcessors(
        array $processorConfiguration,
        string $processorKey,
        array $variables,
        DataSource\DataSourceCollection $collection,
        Frontend\ContentObject\ContentObjectRenderer $contentObjectRenderer,
    ): array {
        // Early return if no processors are registered
        if (!\is_array($processorConfiguration[$processorKey . '.'] ?? null)) {
            return $variables;
        }

        \ksort($processorConfiguration[$processorKey . '.']);

        /** @var string $processorClassName */
        foreach ($processorConfiguration[$processorKey . '.'] as $processorClassName) {
            if (!\is_a($processorClassName, DataSource\DataSourceAwareProcessor::class, true)) {
                throw new Exception\ConfiguredProcessorIsUnsupported($processorClassName);
            }

            $processor = Core\Utility\GeneralUtility::makeInstance($processorClassName);
            $variables = $processor->process($variables, $collection, $contentObjectRenderer);
        }

        return $variables;
    }
}
