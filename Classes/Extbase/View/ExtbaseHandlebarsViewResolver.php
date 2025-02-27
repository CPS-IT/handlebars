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

namespace Fr\Typo3Handlebars\Extbase\View;

use Psr\Container;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Frontend;
use TYPO3Fluid\Fluid;

/**
 * ExtbaseHandlebarsViewResolver
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[DependencyInjection\Attribute\AsAlias(Extbase\Mvc\View\ViewResolverInterface::class)]
final class ExtbaseHandlebarsViewResolver extends Extbase\Mvc\View\GenericViewResolver
{
    private readonly Frontend\ContentObject\ContentObjectRenderer $contentObjectRenderer;

    public function __construct(
        Container\ContainerInterface $container,
        private readonly Extbase\Configuration\ConfigurationManagerInterface $configurationManager,
        private readonly Core\TypoScript\TypoScriptService $typoScriptService,
    ) {
        parent::__construct($container);

        $this->contentObjectRenderer = $container->get(Frontend\ContentObject\ContentObjectRenderer::class);
    }

    public function resolve(
        string $controllerObjectName,
        string $actionName,
        string $format,
        bool $enableFallback = true,
    ): Fluid\View\ViewInterface {
        $handlebarsConfiguration = $this->resolveHandlebarsConfiguration($controllerObjectName, $actionName);

        if ($handlebarsConfiguration !== null || !$enableFallback) {
            return new ExtbaseHandlebarsView(
                $this->contentObjectRenderer,
                $this->typoScriptService,
                $handlebarsConfiguration ?? [],
            );
        }

        return parent::resolve($controllerObjectName, $actionName, $format);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveHandlebarsConfiguration(string $controllerObjectName, string $actionName): ?array
    {
        $configuration = $this->configurationManager->getConfiguration(
            Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
        );
        $controllerAlias = $configuration['controllerConfiguration'][$controllerObjectName]['alias'] ?? null;

        // Early return if controller is not properly registered
        if (!is_string($controllerAlias)) {
            return null;
        }

        $handlebarsConfiguration = $configuration['handlebars'] ?? null;
        $defaultConfiguration = [
            'templateName' => $controllerAlias . '/' . $actionName,
        ];

        // Early return if no handlebars configuration is available
        if (!is_array($handlebarsConfiguration)) {
            return $defaultConfiguration;
        }

        // HANDLEBARSTEMPLATE content object requires TypoScript configuration, so let's convert early
        $typoScriptConfiguration = $this->typoScriptService->convertPlainArrayToTypoScriptArray($handlebarsConfiguration);

        // Resolve template name from controller action
        if (is_string($typoScriptConfiguration['templateName'] ?? null) &&
            is_array($typoScriptConfiguration['templateName.'] ?? null)
        ) {
            // Inject custom fields to be referenced in TypoScript when resolving the
            // template name, e.g. in combination with a CASE content object
            $this->contentObjectRenderer->data['controllerName'] = $controllerAlias;
            $this->contentObjectRenderer->data['controllerObjectName'] = $controllerObjectName;
            $this->contentObjectRenderer->data['controllerAction'] = $actionName;
            $this->contentObjectRenderer->data['controllerNameAndAction'] = $controllerAlias . '::' . $actionName;

            try {
                // Resolve template name based on the current controller action
                $typoScriptConfiguration['templateName'] = $this->contentObjectRenderer->cObjGetSingle(
                    $typoScriptConfiguration['templateName'],
                    $typoScriptConfiguration['templateName.'],
                );
            } finally {
                // Remove configuration which is solely responsible for template name resolving
                unset(
                    $typoScriptConfiguration['templateName.'],
                    $this->contentObjectRenderer->data['controllerName'],
                    $this->contentObjectRenderer->data['controllerObjectName'],
                    $this->contentObjectRenderer->data['controllerAction'],
                    $this->contentObjectRenderer->data['controllerNameAndAction'],
                );
            }
        }

        // Early return if no (valid) template name is given
        if (empty($typoScriptConfiguration['templateName'])) {
            return $defaultConfiguration;
        }

        return $typoScriptConfiguration;
    }
}
