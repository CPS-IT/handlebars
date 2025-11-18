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

/**
 * ViewHelperInvoker
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 */
#[Attribute\AsHelper('viewHelper')]
final readonly class ViewHelperInvoker implements Renderer\Helper\Helper
{
    public function __construct(
        private Fluid\Core\Rendering\RenderingContextFactory $renderingContextFactory,
        private Log\LoggerInterface $logger,
    ) {}

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

        if ($request !== null) {
            $fluidRenderingContext->setAttribute(Message\ServerRequestInterface::class, $request);
        }

        [$namespace, $viewHelperShortName] = \explode(':', $name, 2);
        $className = $fluidRenderingContext->getViewHelperResolver()->createViewHelperInstance($namespace, $viewHelperShortName);

        return $fluidRenderingContext->getViewHelperInvoker()->invoke($className, $options->hash, $fluidRenderingContext, $options->fn);
    }
}
