<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2021 Elias Häußler <e.haeussler@familie-redlich.de>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace Fr\Typo3Handlebars\DependencyInjection\Extension;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * HandlebarsExtension
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 */
final class HandlebarsExtension extends Extension
{
    public const PARAMETER_DEFAULT_DATA = 'handlebars.default_data';
    public const PARAMETER_TEMPLATE_ROOT_PATHS = 'handlebars.template_root_paths';
    public const PARAMETER_PARTIAL_ROOT_PATHS = 'handlebars.partial_root_paths';

    /**
     * @var string[]
     */
    private $templateRootPaths = [];

    /**
     * @var string[]
     */
    private $partialRootPaths = [];

    /**
     * @var array<mixed, mixed>
     */
    private $defaultData = [];

    /**
     * @param array<mixed, mixed>[] $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $this->reset();
        $this->parseConfiguration($configs);

        $container->getParameterBag()->add([
            self::PARAMETER_DEFAULT_DATA => $this->defaultData,
            self::PARAMETER_TEMPLATE_ROOT_PATHS => $this->templateRootPaths,
            self::PARAMETER_PARTIAL_ROOT_PATHS => $this->partialRootPaths,
        ]);
    }

    /**
     * @param array<mixed, mixed>[] $configs
     */
    private function parseConfiguration(array $configs): void
    {
        $templateConfig = $this->mergeConfigs($configs, 'template');
        $this->templateRootPaths = $templateConfig['template_root_paths'] ?? [];
        $this->partialRootPaths = $templateConfig['partial_root_paths'] ?? [];
        $this->defaultData = $this->mergeConfigs($configs, 'default_data');
    }

    /**
     * @param array<mixed, mixed>[] $configs
     * @return array<mixed, mixed>
     */
    private function mergeConfigs(array $configs, string $configKey): array
    {
        $mergedConfig = [];
        foreach (array_column($configs, $configKey) as $concreteConfig) {
            ArrayUtility::mergeRecursiveWithOverrule($mergedConfig, $concreteConfig);
        }
        return $mergedConfig;
    }

    private function reset(): void
    {
        $this->defaultData = [];
        $this->templateRootPaths = [];
        $this->partialRootPaths = [];
    }
}
