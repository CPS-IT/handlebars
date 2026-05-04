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

namespace CPSIT\Typo3Handlebars\View;

use CPSIT\Typo3Handlebars\Controller;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Fluid;
use TYPO3\CMS\Frontend;

/**
 * HandlebarsViewFactory
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[DependencyInjection\Attribute\Autoconfigure(public: true)]
final readonly class HandlebarsViewFactory implements Core\View\ViewFactoryInterface
{
    public function __construct(
        private Extbase\Configuration\ConfigurationManagerInterface $configurationManager,
        #[DependencyInjection\Attribute\Autowire(service: Fluid\View\FluidViewFactory::class)]
        private Core\View\ViewFactoryInterface $delegate,
        private Core\TypoScript\TypoScriptService $typoScriptService,
    ) {}

    public function create(Core\View\ViewFactoryData $data): Core\View\ViewInterface
    {
        $delegate = $this->delegate->create($data);

        return $this->resolveView($data, $delegate) ?? $delegate;
    }

    private function resolveView(Core\View\ViewFactoryData $data, Core\View\ViewInterface $delegate): ?HandlebarsView
    {
        $request = $data->request;
        $contentObjectRenderer = $request?->getAttribute('currentContentObject');

        if ($request === null) {
            return null;
        }

        if (!($contentObjectRenderer instanceof Frontend\ContentObject\ContentObjectRenderer)) {
            return null;
        }

        if ($request instanceof Extbase\Mvc\RequestInterface) {
            $contentObjectConfiguration = $this->resolveExtbaseContentObjectConfiguration(
                $request->getControllerObjectName(),
                $request->getControllerActionName(),
                $data->format ?? $request->getFormat(),
            );
        } else {
            $contentObjectConfiguration = array_filter(
                [
                    'templateName' => $data->templatePathAndFilename,
                    'templateRootPaths.' => $data->templateRootPaths,
                    'partialRootPaths.' => $data->partialRootPaths,
                    'layoutRootPaths.' => $data->layoutRootPaths,
                    'format' => $data->format,
                ],
                static fn(mixed $value) => $value !== null,
            );
        }

        if ($contentObjectConfiguration !== null) {
            return new HandlebarsView(
                $contentObjectRenderer,
                $this->typoScriptService,
                $contentObjectConfiguration,
                $request,
                $delegate,
            );
        }

        return null;
    }

    /**
     * @return array<string|int, mixed>|null
     */
    private function resolveExtbaseContentObjectConfiguration(
        string $controllerObjectName,
        string $actionName,
        string $format,
    ): ?array {
        $configuration = $this->configurationManager->getConfiguration(
            Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
        );
        $controllerAlias = $configuration['controllerConfiguration'][$controllerObjectName]['alias'] ?? null;

        // Early return if controller is not properly registered
        if (!is_string($controllerAlias)) {
            return null;
        }

        // Use hbs as default format, can be overridden with TypoScript
        if ($format === 'html') {
            $format = 'hbs';
        }

        $handlebarsConfiguration = $configuration['handlebars'] ?? null;
        $defaultConfiguration = [
            'templateName' => $controllerAlias . '/' . $actionName,
            'format' => $format,
        ];

        // Early return if no handlebars configuration is available
        if (!is_array($handlebarsConfiguration)) {
            if (is_a($controllerObjectName, Controller\HandlebarsController::class, true)) {
                return $defaultConfiguration;
            }

            return null;
        }

        // HANDLEBARSTEMPLATE content object requires TypoScript configuration, so let's convert early
        $typoScriptConfiguration = $this->typoScriptService->convertPlainArrayToTypoScriptArray($handlebarsConfiguration);

        // Resolve TypoScript configuration based on controller context
        $resolvedConfiguration = [];
        $possibleConfigurationKeys = [
            // Fallback
            'default',
            // Controller only
            $controllerAlias,
            // Controller & action
            $controllerAlias . '::' . $actionName,
            // Controller FQCN
            $controllerObjectName,
        ];
        foreach ($possibleConfigurationKeys as $possibleConfigurationKey) {
            if (is_array($typoScriptConfiguration[$possibleConfigurationKey . '.'] ?? null)) {
                Core\Utility\ArrayUtility::mergeRecursiveWithOverrule(
                    $resolvedConfiguration,
                    $typoScriptConfiguration[$possibleConfigurationKey . '.'],
                );
            }
        }

        // Early return if no configuration was resolved
        if ($resolvedConfiguration === []) {
            return $defaultConfiguration;
        }

        // Add format
        if (!isset($resolvedConfiguration['format'])) {
            $resolvedConfiguration['format'] = $format;
        }

        return $resolvedConfiguration;
    }
}
