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

namespace Fr\Typo3Handlebars\DependencyInjection\Extension;

use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Core;

/**
 * HandlebarsExtension
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 */
final class HandlebarsExtension extends DependencyInjection\Extension\Extension
{
    private const PARAMETER_TEMPLATE_ROOT_PATHS = 'handlebars.templateRootPaths';
    private const PARAMETER_PARTIAL_ROOT_PATHS = 'handlebars.partialRootPaths';
    private const PARAMETER_ROOT_CONTEXT = 'handlebars.variables';

    /**
     * @var string[]
     */
    private array $templateRootPaths = [];

    /**
     * @var string[]
     */
    private array $partialRootPaths = [];

    /**
     * @var array<string|int, mixed>
     */
    private array $rootContext = [];

    /**
     * @param array<string|int, mixed>[] $configs
     */
    public function load(array $configs, DependencyInjection\ContainerBuilder $container): void
    {
        $this->reset();
        $this->parseConfiguration($configs);

        $container->getParameterBag()->add([
            self::PARAMETER_TEMPLATE_ROOT_PATHS => $this->templateRootPaths,
            self::PARAMETER_PARTIAL_ROOT_PATHS => $this->partialRootPaths,
            self::PARAMETER_ROOT_CONTEXT => $this->rootContext,
        ]);
    }

    /**
     * @param array<string|int, mixed>[] $configs
     */
    private function parseConfiguration(array $configs): void
    {
        $templateConfig = $this->mergeConfigs($configs, 'view');

        $this->templateRootPaths = $templateConfig['templateRootPaths'] ?? [];
        $this->partialRootPaths = $templateConfig['partialRootPaths'] ?? [];
        $this->rootContext = $this->mergeConfigs($configs, 'variables');
    }

    /**
     * @param array<string|int, mixed>[] $configs
     * @return array<string|int, mixed>
     */
    private function mergeConfigs(array $configs, string $configKey): array
    {
        $mergedConfig = [];

        foreach (array_column($configs, $configKey) as $concreteConfig) {
            Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($mergedConfig, $concreteConfig);
        }

        return $mergedConfig;
    }

    private function reset(): void
    {
        $this->templateRootPaths = [];
        $this->partialRootPaths = [];
        $this->rootContext = [];
    }
}
