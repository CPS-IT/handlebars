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

namespace CPSIT\Typo3Handlebars\Renderer\Helper\Fluid;

use CPSIT\Typo3Handlebars\Attribute;
use CPSIT\Typo3Handlebars\Renderer;
use DevTheorem\Handlebars;
use Psr\Http\Message;
use Psr\Log;
use TYPO3\CMS\Fluid;
use TYPO3Fluid\Fluid as FluidStandalone;

/**
 * ViewHelperInvoker
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 */
final readonly class ViewHelperInvoker implements Renderer\Helper\Helper
{
    private const LOCAL_NAMESPACES_IDENTIFIER = '_namespaces';

    public function __construct(
        private Fluid\Core\Rendering\RenderingContextFactory $renderingContextFactory,
        private Log\LoggerInterface $logger,
    ) {}

    #[Attribute\AsHelper('viewHelper')]
    public function render(
        Handlebars\HelperOptions $options,
        ?Renderer\RenderingContext $renderingContext = null,
        string $name = '',
    ): mixed {
        if (!str_contains($name, ':')) {
            $this->logger->error(
                'Fluid ViewHelper invoker requires a valid combination of namespace and view helper shortname, e.g. "f:debug", "{name}" given.',
                ['name' => $name],
            );

            return null;
        }

        $fluidRenderingContext = $this->renderingContextFactory->create();
        $renderingContext ??= $options->data['renderingContext'] ?? null;
        $request = null;

        if ($renderingContext instanceof Renderer\RenderingContext) {
            $request = $renderingContext->getRequest();
        }

        // Inject current request
        if ($request !== null) {
            $fluidRenderingContext->setAttribute(Message\ServerRequestInterface::class, $request);
        }

        // Add local namespaces
        if ($renderingContext instanceof Renderer\RenderingContext) {
            /** @var array<string, array<string|null>|null>|null $localNamespaces */
            $localNamespaces = $renderingContext->getVariables()[self::LOCAL_NAMESPACES_IDENTIFIER] ?? null;

            if (is_array($localNamespaces)) {
                $fluidRenderingContext->getViewHelperResolver()->setLocalNamespaces($localNamespaces);
            }
        }

        [$namespace, $viewHelperShortName] = explode(':', $name, 2);
        $className = $fluidRenderingContext->getViewHelperResolver()->createViewHelperInstance($namespace, $viewHelperShortName);

        return $fluidRenderingContext->getViewHelperInvoker()->invoke($className, $options->hash, $fluidRenderingContext, $options->fn);
    }

    #[Attribute\AsHelper('viewHelperNamespace')]
    public function registerNamespace(
        Renderer\RenderingContext $renderingContext,
        string $shortNamespace,
        string $namespaceUrl,
    ): void {
        $localNamespaces = $renderingContext->getVariables()[self::LOCAL_NAMESPACES_IDENTIFIER] ?? [];

        // Early return if local namespaces were modified outside of this helper
        if (!is_array($localNamespaces)) {
            return;
        }

        // Convert namespace declaration to Fluid-compatible template source
        $templateSource = sprintf('<html xmlns:%s="%s"></html>', $shortNamespace, $namespaceUrl);

        // Parse namespace declaration
        $fluidRenderingContext = $this->renderingContextFactory->create();
        $processor = new FluidStandalone\Core\Parser\TemplateProcessor\NamespaceDetectionTemplateProcessor();
        $processor->setRenderingContext($fluidRenderingContext);
        $processor->registerNamespacesFromTemplateSource($templateSource);

        // Merge local namespace declarations
        $localNamespaces = array_merge_recursive(
            $localNamespaces,
            $fluidRenderingContext->getViewHelperResolver()->getLocalNamespaces(),
        );

        $renderingContext->assign(self::LOCAL_NAMESPACES_IDENTIFIER, $localNamespaces);
    }
}
