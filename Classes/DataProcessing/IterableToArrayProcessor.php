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

use Psr\Log;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Frontend;

/**
 * Data processor to convert a given iterable (e.g. a {@see Extbase\Persistence\QueryResultInterface}
 * as returned by an Extbase repository, an {@see Extbase\Persistence\ObjectStorage}, an
 * {@see \Iterator} or a {@see \Generator}) into a plain array.
 *
 * Example:
 * ========
 *
 * Given an Extbase controller assigns a repository query result to the view, e.g.:
 *
 *   $view->assign('news', $this->newsRepository->findAll());
 *
 * the resulting {@see Extbase\Persistence\QueryResultInterface} is available as
 * "news" variable and can be converted into a plain array for use within the Handlebars template.
 * Each converted item is made available as "data" and can be further transformed using nested
 * data processors:
 *
 * plugin.tx_news {
 *   handlebars {
 *     News::list {
 *       # ...
 *
 *       dataProcessing {
 *         10 = iterable-to-array
 *         10 {
 *           iterable = news
 *           as = newsItems
 *
 *           dataProcessing {
 *             10 = object-access
 *             10 {
 *               object = data
 *               path = title
 *               as = title
 *             }
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
#[DependencyInjection\Attribute\AutoconfigureTag('data.processor', ['identifier' => 'iterable-to-array'])]
final readonly class IterableToArrayProcessor implements Frontend\ContentObject\DataProcessorInterface
{
    public function __construct(
        private Log\LoggerInterface $logger,
        private Frontend\ContentObject\ContentDataProcessor $contentDataProcessor,
    ) {}

    /**
     * @param array<string, mixed> $contentObjectConfiguration
     * @param array<string, mixed> $processorConfiguration
     * @param array<string|int, mixed> $processedData
     * @return array<string|int, mixed>
     */
    public function process(
        Frontend\ContentObject\ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData,
    ): array {
        $collection = new DataSource\DataSourceCollection();
        $collection->set(DataSource\DataSource::ContentObjectRenderer, $cObj->data);
        $collection->set(DataSource\DataSource::ContentObjectConfiguration, $contentObjectConfiguration);
        $collection->set(DataSource\DataSource::ProcessedData, $processedData);
        $collection->set(DataSource\DataSource::ProcessorConfiguration, $processorConfiguration);

        $iterableSource = $collection->resolve('iterable', DataSource\DataSource::ProcessorConfiguration);

        // Early return if iterable source is not configured
        if (!is_string($iterableSource) || $iterableSource === '') {
            $this->logger->warning(
                'Invalid iterable source configured for "iterable-to-array" data processor while processing {table}:{uid}.',
                [
                    'table' => $cObj->getCurrentTable(),
                    'uid' => $collection->resolveCurrentUid(),
                ],
            );

            return $processedData;
        }

        /** @var string $as */
        $as = $collection->resolve('as', DataSource\DataSource::ProcessorConfiguration, 'result');
        $preserveKeys = (bool)$collection->resolve('preserveKeys', DataSource\DataSource::ProcessorConfiguration, false);
        $iterable = $collection->resolve($iterableSource);

        // Early return if resolved value is not iterable
        if (!is_iterable($iterable)) {
            $this->logger->warning(
                'Configured value at "{iterableSource}" is not iterable while processing {table}:{uid}.',
                [
                    'iterableSource' => $iterableSource,
                    'table' => $cObj->getCurrentTable(),
                    'uid' => $collection->resolveCurrentUid(),
                ],
            );

            return $processedData;
        }

        $array = is_array($iterable) ? $iterable : iterator_to_array($iterable, $preserveKeys);

        // Process additional data processors for each item
        if (is_array($processorConfiguration['dataProcessing.'] ?? null)) {
            foreach ($array as $key => $item) {
                $array[$key] = $this->contentDataProcessor->process($cObj, $processorConfiguration, ['data' => $item]);
            }
        }

        $processedData[$as] = $preserveKeys ? $array : array_values($array);

        return $processedData;
    }
}
