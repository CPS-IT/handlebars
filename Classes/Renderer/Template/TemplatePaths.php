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

namespace Fr\Typo3Handlebars\Renderer\Template;

use Fr\Typo3Handlebars\Configuration\Extension;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * TemplatePaths
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class TemplatePaths implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public const TEMPLATES = 'template_root_paths';
    public const PARTIALS = 'partial_root_paths';

    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string[]
     */
    protected $templatePaths;

    /**
     * @param ConfigurationManagerInterface $configurationManager
     * @param string $type
     */
    public function __construct(ConfigurationManagerInterface $configurationManager, string $type = self::TEMPLATES)
    {
        $this->configurationManager = $configurationManager;
        $this->type = $type;
    }

    /**
     * @return string[]
     */
    public function get(): array
    {
        if (null === $this->templatePaths) {
            $this->resolveTemplatePaths();
        }

        return $this->templatePaths;
    }

    protected function resolveTemplatePaths(): void
    {
        $this->templatePaths = $this->mergeTemplatePaths(
            $this->getTemplatePathsFromContainer($this->type),
            $this->getTemplatePathsFromTypoScriptConfiguration($this->type)
        );
    }

    /**
     * @param string $type
     * @return string[]
     */
    protected function getTemplatePathsFromContainer(string $type): array
    {
        $parameterName = 'handlebars.' . $type;

        return $this->container->getParameter($parameterName);
    }

    /**
     * @param string $type
     * @return string[]
     */
    protected function getTemplatePathsFromTypoScriptConfiguration(string $type): array
    {
        $configurationType = GeneralUtility::underscoredToLowerCamelCase($type);
        $typoScriptConfiguration = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
            Extension::NAME
        );

        return $typoScriptConfiguration['view'][$configurationType] ?? [];
    }

    /**
     * @param string[] ...$templatePaths
     * @return string[]
     */
    protected function mergeTemplatePaths(array ...$templatePaths): array
    {
        $mergedTemplatePaths = array_replace(...$templatePaths);
        ksort($mergedTemplatePaths);

        return $mergedTemplatePaths;
    }
}
