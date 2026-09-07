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
 * "news" variable in the processed data and can be converted into a plain array for use
 * within the Handlebars template. Each converted item is made available as "currentValue"
 * and can be further transformed using nested data processors:
 *
 * plugin.tx_news {
 *   handlebars {
 *     News::list {
 *       # ...
 *
 *       dataProcessing {
 *         10 = iterable-to-array
 *         10 {
 *           iterable = processedData:news
 *           as = newsItems
 *
 *           dataProcessing {
 *             10 = object-access
 *             10 {
 *               object = contentObjectConfiguration:currentValue
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
        private DataSource\DataSourceProvider $dataSourceProvider,
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
        $collection = DataSource\DataSourceCollection::for(
            $cObj,
            $contentObjectConfiguration,
            $processorConfiguration,
            $processedData,
        );

        /** @var string $as */
        $as = $collection->resolve('as', DataSource\DataSource::ProcessorConfiguration, 'result');
        $preserveKeys = (bool)$collection->resolve('preserveKeys', DataSource\DataSource::ProcessorConfiguration, false);
        $dataProcessing = $collection->resolve('dataProcessing.', DataSource\DataSource::ProcessorConfiguration);
        $iterable = null;

        try {
            $iterable = $this->dataSourceProvider->provide($collection, 'iterable');
        } catch (Exception\DataSourceIsMissingInCollection $exception) {
            $this->logger->warning(
                'No variables provided for data source "{source}" while processing {table}:{uid}.',
                [
                    'source' => $exception->dataSource->value,
                    'table' => $cObj->getCurrentTable(),
                    'uid' => $collection->resolveCurrentUid(),
                ],
            );
        } catch (Exception\DataSourceIsNotSupported $exception) {
            $this->logger->warning(
                'Invalid data source keyword "{source}" passed while processing {table}:{uid}.',
                [
                    'source' => $exception->dataSourceIdentifier,
                    'table' => $cObj->getCurrentTable(),
                    'uid' => $collection->resolveCurrentUid(),
                ],
            );
        } catch (Exception\PathIsMissingInDataSource $exception) {
            $this->logger->warning(
                'Invalid path "{path}" for data source "{source}" passed while processing {table}:{uid}.',
                [
                    'path' => $exception->path,
                    'source' => $exception->dataSource->value,
                    'table' => $cObj->getCurrentTable(),
                    'uid' => $collection->resolveCurrentUid(),
                ],
            );
        }

        // Early return if resolved value is not iterable
        if (!is_iterable($iterable)) {
            $this->logger->warning(
                'Invalid iterable configured for "iterable-to-array" data processor while processing {table}:{uid}.',
                [
                    'table' => $cObj->getCurrentTable(),
                    'uid' => $collection->resolveCurrentUid(),
                ],
            );

            return $processedData;
        }

        $array = is_array($iterable) ? $iterable : iterator_to_array($iterable, $preserveKeys);

        // Process additional data processors for each item
        if (is_array($dataProcessing)) {
            foreach ($array as $key => $item) {
                $array[$key] = $this->contentDataProcessor->process(
                    $cObj,
                    [
                        'dataProcessing.' => $dataProcessing,
                        'currentValue' => $item,
                    ],
                    [],
                );
            }
        }

        $processedData[$as] = $preserveKeys ? $array : array_values($array);

        return $processedData;
    }
}
