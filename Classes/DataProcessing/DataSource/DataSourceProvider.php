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

namespace CPSIT\Typo3Handlebars\DataProcessing\DataSource;

use CPSIT\Typo3Handlebars\Exception;
use TYPO3\CMS\Core;

/**
 * DataSourceProvider
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final readonly class DataSourceProvider
{
    public function __construct(
        private Core\TypoScript\TypoScriptService $typoScriptService,
    ) {}

    /**
     * @return array<string|int, mixed>|null
     * @throws Exception\DataSourceIsMissingInCollection
     * @throws Exception\DataSourceIsNotSupported
     * @throws Exception\PathIsMissingInDataSource
     */
    public function provide(DataSourceCollection $collection): ?array
    {
        /** @var array<string|int, mixed>|null $dataFromConfiguration */
        $dataFromConfiguration = $collection->resolve('data.', DataSource::ProcessorConfiguration);
        /** @var array<string|int, mixed>|null $dataFromProcessedData */
        $dataFromProcessedData = $collection->resolve('data', DataSource::ProcessedData);
        /** @var string|array<int, string>|null $dataSources */
        $dataSources = $collection->resolve('dataSource.', DataSource::ProcessorConfiguration)
            ?? $collection->resolve('dataSource', DataSource::ProcessorConfiguration);

        // Early return if no data sources are configured
        if ($dataSources === null) {
            return $dataFromConfiguration ?? $dataFromProcessedData;
        }

        // Normalize content object configuration
        $normalizedCollection = clone $collection;
        $normalizedCollection->set(
            DataSource::ContentObjectConfiguration,
            $this->typoScriptService->convertTypoScriptArrayToPlainArray(
                $collection->get(DataSource::ContentObjectConfiguration),
            ),
        );

        // Normalize simplified data source configuration
        if (!is_array($dataSources)) {
            $dataSources = [$dataSources];
        }

        ksort($dataSources);

        return array_reduce(
            $dataSources,
            fn(?array $carry, string $keyword) => $this->processDataSource($carry, $keyword, $normalizedCollection),
        );
    }

    /**
     * @param array<string|int, mixed>|null $processedDataSources
     * @return array<string|int, mixed>
     * @throws Exception\DataSourceIsMissingInCollection
     * @throws Exception\DataSourceIsNotSupported
     * @throws Exception\PathIsMissingInDataSource
     */
    private function processDataSource(
        ?array $processedDataSources,
        string $dataSourceIdentifier,
        DataSourceCollection $collection,
    ): array {
        if (str_contains($dataSourceIdentifier, ':')) {
            [$dataSourceIdentifier, $path] = Core\Utility\GeneralUtility::trimExplode(':', $dataSourceIdentifier, true, 2);
        } else {
            $path = null;
        }

        $dataSource = DataSource::tryFrom($dataSourceIdentifier)
            ?? throw new Exception\DataSourceIsNotSupported($dataSourceIdentifier);

        if (!$collection->has($dataSource)) {
            throw new Exception\DataSourceIsMissingInCollection($dataSource);
        }

        $data = $collection->get($dataSource);

        // Limit data to configured path
        if ($path !== null) {
            try {
                $data = Core\Utility\ArrayUtility::getValueByPath($data, $path, '.');
            } catch (Core\Utility\Exception\MissingArrayPathException $exception) {
                throw new Exception\PathIsMissingInDataSource($path, $dataSource, $exception);
            }
        }

        if (!is_array($processedDataSources)) {
            $processedDataSources = [];
        }

        Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($processedDataSources, $data);

        return $processedDataSources;
    }
}
