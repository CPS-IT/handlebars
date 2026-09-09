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
use TYPO3\CMS\Frontend;

/**
 * DataSourceCollection
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class DataSourceCollection
{
    /**
     * @var array<value-of<DataSource>, array<string|int, mixed>>
     */
    private array $dataSources = [];

    /**
     * @param array<string, mixed>|null $contentObjectConfiguration
     * @param array<string, mixed>|null $processorConfiguration
     * @param array<string|int, mixed>|null $processedData
     */
    public static function for(
        ?Frontend\ContentObject\ContentObjectRenderer $cObj = null,
        ?array $contentObjectConfiguration = null,
        ?array $processorConfiguration = null,
        ?array $processedData = null,
    ): self {
        $collection = new self();

        if ($cObj !== null) {
            $collection->set(DataSource::ContentObjectRenderer, $cObj->data);
        }
        if ($contentObjectConfiguration !== null) {
            $collection->set(DataSource::ContentObjectConfiguration, $contentObjectConfiguration);
        }
        if ($processedData !== null) {
            $collection->set(DataSource::ProcessedData, $processedData);
        }
        if ($processorConfiguration !== null) {
            $collection->set(DataSource::ProcessorConfiguration, $processorConfiguration);
        }

        return $collection;
    }

    /**
     * @return array<string|int, mixed>
     */
    public function get(DataSource $dataSource): array
    {
        return $this->dataSources[$dataSource->value] ?? [];
    }

    /**
     * @param array<string|int, mixed> $configuration
     */
    public function set(DataSource $dataSource, array $configuration): self
    {
        $this->dataSources[$dataSource->value] = $configuration;

        return $this;
    }

    public function has(DataSource $dataSource): bool
    {
        return array_key_exists($dataSource->value, $this->dataSources);
    }

    public function remove(DataSource $dataSource): self
    {
        unset($this->dataSources[$dataSource->value]);

        return $this;
    }

    /**
     * @template T
     * @param non-empty-string $key
     * @param DataSource|list<DataSource> $dataSources
     * @param T $default
     * @return mixed|T
     * @throws Exception\PathIsMissingInDataSource
     */
    public function resolve(
        string $key,
        DataSource|array $dataSources = [],
        mixed $default = null,
        bool $optional = true,
    ): mixed {
        // Get from all configured data sources (in the given order) if no data sources are configured explicitly
        // The order can be seen as priority for each single data source
        if ($dataSources === []) {
            $dataSources = $this->getConfiguredDataSourcesSortedByPriority();
        } elseif ($dataSources instanceof DataSource) {
            $dataSources = [$dataSources];
        }

        $exception = null;

        foreach ($dataSources as $dataSource) {
            try {
                return $this->resolveForDataSource($key, $dataSource);
            } catch (Exception\PathIsMissingInDataSource $exception) {
                // Store exception and throw later after all possible data sources have been iterated.
            }
        }

        // If data could not be resolved finally and no default is configured, throw a dedicated exception.
        // This rather strict behavior can be bypassed by passing either a default value or $optional = true.
        if ($exception !== null && $default === null && !$optional) {
            throw $exception;
        }

        return $default;
    }

    public function resolveCurrentUid(): int|string
    {
        $uid = $this->resolve('uid', DataSource::ContentObjectRenderer);

        if (!is_numeric($uid)) {
            return '*unknown*';
        }

        return (int)$uid;
    }

    /**
     * @param DataSource|list<DataSource> $dataSources
     */
    public function with(string $key, mixed $value, DataSource|array $dataSources = []): self
    {
        // Apply to all configured data sources if no data sources are configured explicitly
        if ($dataSources === []) {
            foreach ($this->dataSources as $dataSource => $configuration) {
                $this->dataSources[$dataSource][$key] = $value;
            }

            return $this;
        }

        if ($dataSources instanceof DataSource) {
            $dataSources = [$dataSources];
        }

        foreach ($dataSources as $dataSource) {
            $this->dataSources[$dataSource->value] ??= [];
            $this->dataSources[$dataSource->value][$key] = $value;
        }

        return $this;
    }

    /**
     * @throws Exception\PathIsMissingInDataSource
     */
    private function resolveForDataSource(string $key, DataSource $dataSource): mixed
    {
        $configuration = $this->get($dataSource);

        try {
            return Core\Utility\ArrayUtility::getValueByPath($configuration, $key);
        } catch (Core\Utility\Exception\MissingArrayPathException $exception) {
            throw new Exception\PathIsMissingInDataSource($key, $dataSource, $exception);
        }
    }

    /**
     * @return list<DataSource>
     */
    private function getConfiguredDataSourcesSortedByPriority(): array
    {
        $dataSources = array_map(DataSource::from(...), array_keys($this->dataSources));

        return DataSource::sortByPriority($dataSources);
    }
}
