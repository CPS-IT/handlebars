<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2025 Elias Häußler <e.haeussler@familie-redlich.de>
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

namespace Fr\Typo3Handlebars\Frontend\ContentObject;

use Fr\Typo3Handlebars\Renderer;
use TYPO3\CMS\Core;
use TYPO3\CMS\Frontend;

/**
 * HandlebarsTemplateContentObject
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class HandlebarsTemplateContentObject extends Frontend\ContentObject\FluidTemplateContentObject
{
    public function __construct(
        Frontend\ContentObject\ContentDataProcessor $contentDataProcessor,
        private readonly Renderer\Renderer $renderer,
        private readonly Renderer\Template\Path\ContentObjectPathProvider $pathProvider,
        private readonly Core\TypoScript\TypoScriptService $typoScriptService,
    ) {
        parent::__construct($contentDataProcessor);
    }

    /**
     * @param array<string, mixed> $conf
     */
    public function render($conf = []): string
    {
        if (!is_array($conf)) {
            $conf = [];
        }

        // Create handlebars view
        $view = $this->createView($conf);

        // Resolve template paths
        /** @var array<string, mixed> $templatePaths */
        $templatePaths = $this->typoScriptService->convertTypoScriptArrayToPlainArray(
            array_intersect_key(
                $conf,
                [
                    'partialRootPath' => true,
                    'partialRootPaths.' => true,
                    'templateRootPath' => true,
                    'templateRootPaths.' => true,
                ],
            ),
        );

        // Populate template paths for availability in subsequent renderings
        $this->pathProvider->push($templatePaths);

        $view->assignMultiple($this->resolveVariables($conf));

        $this->renderPageAssetsIntoPageRenderer($conf, $view);

        try {
            $content = $this->renderer->render($view);
        } finally {
            // Remove current content object rendering from path provider stack
            $this->pathProvider->pop();
        }

        return $this->applyStandardWrapToRenderedContent($content, $conf);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function createView(array $config): Renderer\Template\View\HandlebarsView
    {
        $format = $this->cObj?->stdWrapValue('format', $config, null);
        $view = new Renderer\Template\View\HandlebarsView();

        if (is_string($format)) {
            $view->setFormat($format);
        }

        if (isset($config['templateName']) || isset($config['templateName.'])) {
            return $view->setTemplatePath(
                (string)$this->cObj?->stdWrapValue('templateName', $config),
            );
        }

        if (isset($config['template']) || isset($config['template.'])) {
            return $view->setTemplateSource(
                (string)$this->cObj?->stdWrapValue('template', $config),
            );
        }

        if (isset($config['file']) || isset($config['file.'])) {
            return $view->setTemplatePath(
                (string)$this->cObj?->stdWrapValue('file', $config),
            );
        }

        return $view;
    }

    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    private function resolveVariables(array $config): array
    {
        $variables = $this->getContentObjectVariables($config);

        // Resolve variables from simple hierarchy (without content objects)
        $simpleVariables = \array_diff_key(
            $this->typoScriptService->convertTypoScriptArrayToPlainArray($config['variables.'] ?? []),
            $variables,
        );

        // Merge variables
        if ($simpleVariables !== []) {
            Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($variables, $simpleVariables);
        }

        // Process variables with configured data processors
        if ($this->cObj !== null) {
            $variables = $this->contentDataProcessor->process($this->cObj, $config, $variables);
        }

        if (isset($config['settings.'])) {
            $variables['settings'] = $this->typoScriptService->convertTypoScriptArrayToPlainArray($config['settings.']);
        }

        return $variables;
    }

    /**
     * @param array<string, mixed> $config
     */
    private function renderPageAssetsIntoPageRenderer(array $config, Renderer\Template\View\HandlebarsView $baseView): void
    {
        $headerAssets = $this->renderAssets($config['headerAssets.'] ?? [], $baseView);
        $footerAssets = $this->renderAssets($config['footerAssets.'] ?? [], $baseView);

        if (\trim($headerAssets) !== '') {
            $this->getPageRenderer()->addHeaderData($headerAssets);
        }

        if (\trim($footerAssets) !== '') {
            $this->getPageRenderer()->addFooterData($footerAssets);
        }
    }

    /**
     * @param array<string, mixed> $config
     */
    private function renderAssets(array $config, Renderer\Template\View\HandlebarsView $baseView): string
    {
        if ($config === []) {
            return '';
        }

        $view = $this->createView($config);
        $view->assignMultiple($baseView->getVariables());

        return $this->renderer->render($view);
    }
}
