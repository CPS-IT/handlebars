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
     */
    public function resolve(string $key, DataSource|array $dataSources = [], mixed $default = null): mixed
    {
        // Get from all configured data sources (in the given order) if no data sources are configured explicitly
        // The order can be seen as priority for each single data source
        if ($dataSources === []) {
            $dataSources = $this->getConfiguredDataSourcesSortedByPriority();
        } elseif ($dataSources instanceof DataSource) {
            $dataSources = [$dataSources];
        }

        foreach ($dataSources as $dataSource) {
            $found = false;
            $result = $this->resolveForDataSource($key, $dataSource, $found);

            if ($found) {
                return $result;
            }
        }

        return $default;
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

    private function resolveForDataSource(string $key, DataSource $dataSource, bool &$found = false): mixed
    {
        $configuration = $this->get($dataSource);
        $found = array_key_exists($key, $configuration);

        return $found ? $configuration[$key] : null;
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
