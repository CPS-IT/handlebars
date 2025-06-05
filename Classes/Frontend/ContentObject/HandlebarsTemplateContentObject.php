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

namespace Fr\Typo3Handlebars\Frontend\ContentObject;

use Fr\Typo3Handlebars\Exception;
use Fr\Typo3Handlebars\Renderer;
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

        $this->renderPageAssetsIntoPageRenderer($conf);

        try {
            $content = $this->renderer->render($view);
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
        // Process content object variables and simple variables
        if (\is_array($config['variables.'] ?? null)) {
            $variables = $this->processVariables($config['variables.']);
        } else {
            $variables = $this->getContentObjectVariables($config);
        }

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
     * @param array<string, mixed> $variables
     * @return array<string, mixed>
     */
    private function processVariables(array $variables): array
    {
        $contentObjectRenderer = $this->getContentObjectRenderer();
        $variablesToProcess = [];
        $simpleVariables = [];

        foreach ($variables as $name => $value) {
            if (isset($variablesToProcess[$name])) {
                continue;
            }

            // Use sanitized variable name for simple variables
            $sanitizedName = \rtrim($name, '.');

            // Apply variable as simple variable if it's a complex structure (such as objects)
            if (!is_string($value) && !\is_array($value)) {
                $simpleVariables[$sanitizedName] = $value;

                continue;
            }

            // Register variable for further processing if an appropriate content object is available
            // or if variable is a reference to another variable (will be resolved later)
            if (is_string($value) &&
                ($contentObjectRenderer->getContentObject($value) !== null || str_starts_with($value, '<'))
            ) {
                $cObjConfName = $name . '.';
                $variablesToProcess[$name] = $value;

                if (isset($variables[$cObjConfName])) {
                    $variablesToProcess[$cObjConfName] = $variables[$cObjConfName];
                }

                continue;
            }

            // Apply variable as simple variable if it's a simple construct
            // (including arrays, which will be processed recursively as they may contain content objects)
            if (\is_array($value)) {
                $simpleVariables[$sanitizedName] = $this->processVariables($value);

                unset($simpleVariables[$sanitizedName]['data']);
                unset($simpleVariables[$sanitizedName]['current']);
            } else {
                $simpleVariables[$sanitizedName] = $value;
            }
        }

        // Process content object variables
        $processedVariables = $this->getContentObjectVariables(['variables.' => $variablesToProcess]);

        // Merged processed content object variables with simple variables
        Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($processedVariables, $simpleVariables);

        return $processedVariables;
    }

    /**
     * @param array<string, mixed> $conf
     * @return array<string, mixed>
     * @throws Exception\ReservedVariableCannotBeUsed
     * @see https://github.com/TYPO3/typo3/blob/v13.4.13/typo3/sysext/frontend/Classes/ContentObject/FluidTemplateContentObject.php#L228
     */
    private function getContentObjectVariables(array $conf): array
    {
        if ($this->cObj === null) {
            return [];
        }

        $variables = [];
        $reservedVariables = ['data', 'current'];
        $variablesToProcess = (array)($conf['variables.'] ?? []);

        foreach ($variablesToProcess as $variableName => $cObjType) {
            if (is_array($cObjType)) {
                continue;
            }

            if (!in_array($variableName, $reservedVariables, true)) {
                $cObjConf = $variablesToProcess[$variableName . '.'] ?? [];
                $variables[$variableName] = $this->cObj->cObjGetSingle($cObjType, $cObjConf, 'variables.' . $variableName);
            } else {
                throw new Exception\ReservedVariableCannotBeUsed($variableName);
            }
        }

        $variables['data'] = $this->cObj->data;
        $variables['current'] = $this->cObj->data[$this->cObj->currentValKey] ?? null;

        return $variables;
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
