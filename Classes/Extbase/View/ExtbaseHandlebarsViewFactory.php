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

namespace CPSIT\Typo3Handlebars\Extbase\View;

use CPSIT\Typo3Handlebars\Controller;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Fluid;
use TYPO3\CMS\Frontend;

/**
 * ExtbaseHandlebarsViewFactory
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[DependencyInjection\Attribute\Autoconfigure(public: true)]
final readonly class ExtbaseHandlebarsViewFactory implements Core\View\ViewFactoryInterface
{
    public function __construct(
        private Extbase\Configuration\ConfigurationManagerInterface $configurationManager,
        #[DependencyInjection\Attribute\Autowire(service: Fluid\View\FluidViewFactory::class)]
        private Core\View\ViewFactoryInterface $delegate,
        private Core\TypoScript\TypoScriptService $typoScriptService,
    ) {}

    public function create(Core\View\ViewFactoryData $data): Core\View\ViewInterface
    {
        if (!($data->request instanceof Extbase\Mvc\RequestInterface)) {
            return $this->delegate->create($data);
        }

        return $this->resolveView($data) ?? $this->delegate->create($data);
    }

    private function resolveView(Core\View\ViewFactoryData $data): ?ExtbaseHandlebarsView
    {
        /** @var Extbase\Mvc\RequestInterface $request */
        $request = $data->request;
        $contentObjectRenderer = $request->getAttribute('currentContentObject');

        if (!($contentObjectRenderer instanceof Frontend\ContentObject\ContentObjectRenderer)) {
            return null;
        }

        $contentObjectConfiguration = $this->resolveContentObjectConfiguration(
            $contentObjectRenderer,
            $request->getControllerObjectName(),
            $request->getControllerActionName(),
            $data->format ?? $request->getFormat(),
        );

        if ($contentObjectConfiguration !== null) {
            return new ExtbaseHandlebarsView($contentObjectRenderer, $this->typoScriptService, $contentObjectConfiguration);
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveContentObjectConfiguration(
        Frontend\ContentObject\ContentObjectRenderer $contentObjectRenderer,
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

        // Resolve template name from controller action
        if (is_string($typoScriptConfiguration['templateName'] ?? null) &&
            is_array($typoScriptConfiguration['templateName.'] ?? null)
        ) {
            // Inject custom fields to be referenced in TypoScript when resolving the
            // template name, e.g. in combination with a CASE content object
            $contentObjectRenderer->data['controllerName'] = $controllerAlias;
            $contentObjectRenderer->data['controllerObjectName'] = $controllerObjectName;
            $contentObjectRenderer->data['controllerAction'] = $actionName;
            $contentObjectRenderer->data['controllerNameAndAction'] = $controllerAlias . '::' . $actionName;

            try {
                // Resolve template name based on the current controller action
                $typoScriptConfiguration['templateName'] = $contentObjectRenderer->cObjGetSingle(
                    $typoScriptConfiguration['templateName'],
                    $typoScriptConfiguration['templateName.'],
                );
            } finally {
                // Remove configuration which is solely responsible for template name resolving
                unset(
                    $typoScriptConfiguration['templateName.'],
                    $contentObjectRenderer->data['controllerName'],
                    $contentObjectRenderer->data['controllerObjectName'],
                    $contentObjectRenderer->data['controllerAction'],
                    $contentObjectRenderer->data['controllerNameAndAction'],
                );
            }
        }

        // Early return if no (valid) template name is given
        if (empty($typoScriptConfiguration['templateName'])) {
            return $defaultConfiguration;
        }

        // Add format
        if (!isset($typoScriptConfiguration['format'])) {
            $typoScriptConfiguration['format'] = $format;
        }

        return $typoScriptConfiguration;
    }
}
