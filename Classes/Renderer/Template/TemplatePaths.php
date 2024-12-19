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
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * TemplatePaths
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class TemplatePaths
{
    public const TEMPLATES = 'template_root_paths';
    public const PARTIALS = 'partial_root_paths';

    /**
     * @var string[]
     */
    protected ?array $templatePaths = null;

    /**
     * @param array{
     *     partial_root_paths?: array<int, string>,
     *     template_root_paths?: array<int, string>,
     * } $viewConfiguration
     */
    public function __construct(
        protected readonly ConfigurationManagerInterface $configurationManager,
        #[Autowire([
            self::TEMPLATES => '%handlebars.template_root_paths%',
            self::PARTIALS => '%handlebars.partial_root_paths%',
        ])]
        protected readonly array $viewConfiguration = [],
        protected readonly string $type = self::TEMPLATES,
    ) {}

    /**
     * @return string[]
     */
    public function get(): array
    {
        if ($this->templatePaths === null) {
            $this->resolveTemplatePaths();
        }

        return $this->templatePaths;
    }

    /**
     * @phpstan-assert string[] $this->templatePaths
     */
    protected function resolveTemplatePaths(): void
    {
        $this->templatePaths = $this->mergeTemplatePaths(
            $this->getTemplatePathsFromViewConfiguration($this->type),
            $this->getTemplatePathsFromTypoScriptConfiguration($this->type)
        );
    }

    /**
     * @return string[]
     */
    protected function getTemplatePathsFromViewConfiguration(string $type): array
    {
        return $this->viewConfiguration[$type] ?? [];
    }

    /**
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
