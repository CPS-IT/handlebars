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

namespace CPSIT\Typo3Handlebars\Frontend\ContentObject;

use CPSIT\Typo3Handlebars\Exception;
use CPSIT\Typo3Handlebars\Renderer;
use CPSIT\Typo3Handlebars\Service;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Core;
use TYPO3\CMS\Frontend;

/**
 * HandlebarsTemplateContentObject
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[DependencyInjection\Attribute\AutoconfigureTag('frontend.contentobject', ['identifier' => 'HANDLEBARSTEMPLATE'])]
final class HandlebarsTemplateContentObject extends Frontend\ContentObject\AbstractContentObject
{
    public function __construct(
        private readonly Frontend\ContentObject\ContentDataProcessor $contentDataProcessor,
        private readonly Renderer\Template\Path\ContentObjectPathProvider $pathProvider,
        private readonly Renderer\Renderer $renderer,
        private readonly Core\TypoScript\TypoScriptService $typoScriptService,
        private readonly Service\AssetService $assetService,
    ) {}

    /**
     * @param array<string, mixed> $conf
     */
    public function render($conf = []): string
    {
        /* @phpstan-ignore function.alreadyNarrowedType */
        if (!\is_array($conf)) {
            $conf = [];
        }

        // Create rendering context
        $context = $this->createContext($conf);

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

        $context->assignMultiple($this->resolveVariables($conf));

        // Process modern AssetCollector-based assets
        $this->processAssets($conf);

        // Process legacy headerAssets/footerAssets (maintained for backward compatibility)
        $this->renderPageAssetsIntoPageRenderer($conf);

        try {
            $content = $this->renderer->render($context);
        } finally {
            // Remove current content object rendering from path provider stack
            $this->pathProvider->pop();
        }

        if (isset($conf['stdWrap.'])) {
            return $this->cObj?->stdWrap($content, $conf['stdWrap.']) ?? $content;
        }

        return $content;
    }

    /**
     * @param array<string, mixed> $config
     */
    private function createContext(array $config): Renderer\RenderingContext
    {
        $format = $this->cObj?->stdWrapValue('format', $config, null);
        $context = new Renderer\RenderingContext(request: $this->request);

        if (is_string($format)) {
            $context->setFormat($format);
        }

        if (isset($config['templateName']) || isset($config['templateName.'])) {
            return $context->setTemplatePath(
                (string)$this->cObj?->stdWrapValue('templateName', $config),
            );
        }

        if (isset($config['template']) || isset($config['template.'])) {
            return $context->setTemplateSource(
                (string)$this->cObj?->stdWrapValue('template', $config),
            );
        }

        if (isset($config['file']) || isset($config['file.'])) {
            return $context->setTemplatePath(
                (string)$this->cObj?->stdWrapValue('file', $config),
            );
        }

        return $context;
    }

    /**
     * @param array<string, mixed> $config
     * @return array<string|int, mixed>
     */
    private function resolveVariables(array $config): array
    {
        // Process content object variables and simple variables
        if ($this->cObj !== null && \is_array($config['variables.'] ?? null)) {
            $processor = Renderer\Variables\VariablesProcessor::for($this->cObj);
            $variables = $processor->process($config['variables.']);
        } else {
            $variables = [];
        }

        // Add current context variables
        $variables['data'] = $this->cObj->data ?? [];
        $variables['current'] = $this->cObj?->data[$this->cObj->currentValKey] ?? null;

        // Process variables with configured data processors
        if ($this->cObj !== null) {
            $variables = $this->contentDataProcessor->process($this->cObj, $config, $variables);
        }

        // Make settings available as variables
        if (isset($config['settings.'])) {
            $variables['settings'] = $this->typoScriptService->convertTypoScriptArrayToPlainArray($config['settings.']);
        }

        return $variables;
    }

    /**
     * Process and register assets from TypoScript configuration.
     *
     * @param array<string, mixed> $config
     * @throws Exception\InvalidAssetConfigurationException
     */
    private function processAssets(array $config): void
    {
        if (!isset($config['assets.']) || !\is_array($config['assets.'])) {
            return;
        }

        try {
            $this->assetService->registerAssets(
                $this->typoScriptService->convertTypoScriptArrayToPlainArray($config['assets.'])
            );
        } catch (Exception\InvalidAssetConfigurationException $e) {
            throw $e;
        }
    }

    /**
     * @param array<string, mixed> $config
     */
    private function renderPageAssetsIntoPageRenderer(array $config): void
    {
        if (is_string($config['headerAssets'] ?? null) && is_array($config['headerAssets.'] ?? null)) {
            $headerAssets = $this->cObj?->cObjGetSingle($config['headerAssets'], $config['headerAssets.']) ?? '';
        } else {
            $headerAssets = '';
        }

        if (is_string($config['footerAssets'] ?? null) && is_array($config['footerAssets.'] ?? null)) {
            $footerAssets = $this->cObj?->cObjGetSingle($config['footerAssets'], $config['footerAssets.']) ?? '';
        } else {
            $footerAssets = '';
        }

        if (\trim($headerAssets) !== '') {
            $this->getPageRenderer()->addHeaderData($headerAssets);
        }

        if (\trim($footerAssets) !== '') {
            $this->getPageRenderer()->addFooterData($footerAssets);
        }
    }
}
