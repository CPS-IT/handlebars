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
use TYPO3\CMS\Frontend;

/**
 * Data processor to iterate over an array (or other iterable) and, for each item, evaluate a
 * "variables" configuration block and/or run a nested "dataProcessing" chain against that item.
 *
 * Example:
 * ========
 *
 * tt_content.textpic = HANDLEBARSTEMPLATE
 * tt_content.textpic {
 *   templateName = @textpic
 *
 *   dataProcessing {
 *     10 = files
 *     10 {
 *       references.fieldName = image
 *       as = files
 *     }
 *
 *     20 = process-each
 *     20 {
 *       dataSource = processedData:files
 *       as = processedFiles
 *
 *       dataProcessing {
 *         10 = object-access
 *         10 {
 *           object = contentObjectConfiguration:currentValue
 *           path = publicUrl
 *           as = url
 *         }
 *       }
 *     }
 *   }
 * }
 *
 * Each file reference resolved by the core "files" processor is made available to the nested
 * "dataProcessing" chain as "currentValue", so "object-access" can pull individual properties off it.
 * The per-item results are collected — keyed by the original array keys — under "processedFiles".
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[DependencyInjection\Attribute\AutoconfigureTag('data.processor', ['identifier' => 'process-each'])]
final readonly class ProcessEachProcessor implements Frontend\ContentObject\DataProcessorInterface
{
    use DataSource\SupportsDataSource;
    use DataSource\SupportsDataSourceAwareProcessing;

    public function __construct(
        private Frontend\ContentObject\ContentDataProcessor $contentDataProcessor,
        private DataSource\DataSourceProvider $dataSourceProvider,
        private Log\LoggerInterface $logger,
    ) {}

    /**
     * @param array<string, mixed> $contentObjectConfiguration
     * @param array<string, mixed> $processorConfiguration
     * @param array<string, mixed> $processedData
     * @return array<string, mixed>
     * @throws Exception\ReservedVariableCannotBeUsed
     * @throws Frontend\ContentObject\Exception\ContentRenderingException
     */
    public function process(
        Frontend\ContentObject\ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData,
    ): array {
        $collection = DataSource\DataSourceCollection::for(
            $cObj,
            $contentObjectConfiguration,
            $processorConfiguration,
            $processedData,
        );

        $data = $this->provideData($cObj, $collection);

        // Early return if resolved variables are not iterable
        if (!is_iterable($data)) {
            return $processedData;
        }

        /** @var string $as */
        $as = $collection->resolve('as', DataSource\DataSource::ProcessorConfiguration, 'result');
        $variables = $collection->resolve('variables.', DataSource\DataSource::ProcessorConfiguration);
        $dataProcessing = $collection->resolve('dataProcessing.', DataSource\DataSource::ProcessorConfiguration);

        // Early return if neither "variables." nor "dataProcessing." are properly defined
        if (!is_array($variables) && !is_array($dataProcessing)) {
            $processedData[$as] = $data;

            return $processedData;
        }

        $processedVariables = [];
        $currentValue = $cObj->getCurrentVal();

        try {
            // Process each variable (both "variables." as well as "dataProcessing." are respected)
            /** @var array-key $key */
            foreach ($data as $key => $value) {
                $cObj->setCurrentVal($value);

                // Process "variables."
                if (is_array($variables)) {
                    $processed = Renderer\Variables\VariablesProcessor::for($cObj)->process($variables);
                } else {
                    $processed = [];
                }

                // Process "dataProcessing."
                if (is_array($dataProcessing)) {
                    $processed = $this->contentDataProcessor->process(
                        $cObj,
                        [
                            'dataProcessing.' => $dataProcessing,
                            'currentValue' => $value,
                        ],
                        $processed,
                    );
                }

                $processedVariables[$key] = $processed;
            }
        } finally {
            // Restore current value
            $cObj->setCurrentVal($currentValue);
        }

        // Apply processed variables
        $processedData[$as] = $processedVariables;

        return $processedData;
    }
}
