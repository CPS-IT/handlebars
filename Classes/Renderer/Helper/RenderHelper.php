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

namespace CPSIT\Typo3Handlebars\Renderer\Helper;

use CPSIT\Typo3Handlebars\Attribute;
use CPSIT\Typo3Handlebars\Renderer;
use DevTheorem\Handlebars;
use TYPO3\CMS\Core;

/**
 * RenderHelper
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @see https://github.com/frctl/fractal/blob/main/packages/handlebars/src/helpers/render.js
 */
#[Attribute\AsHelper('render')]
final readonly class RenderHelper implements Helper
{
    public function __construct(
        private Renderer\Renderer $renderer,
    ) {}

    public function render(Handlebars\HelperOptions $options, string $name = '', mixed ...$arguments): Handlebars\SafeString
    {
        // Resolve data
        $rootData = $options->data['root'];
        $merge = (bool)($options->hash['merge'] ?? false);

        // Fetch custom context
        // ====================
        // Custom contexts can be defined as helper argument, e.g.
        // {{render '@foo' customContext}}
        $subContext = reset($arguments);
        if (!\is_array($subContext)) {
            $subContext = [];
        }

        // Fetch default context
        // =====================
        // Default contexts can be defined by using the template name when rendering a
        // specific template, e.g. if $name = '@foo' then $rootData['@foo'] is requested
        $defaultContext = $rootData[$name] ?? [];

        // Resolve context
        // ===============
        // Use default context as new context if no custom context is given, otherwise
        // merge both contexts in case merge=true is passed as helper option, e.g.
        // {{render '@foo' customContext merge=true}}
        if ($subContext === []) {
            $subContext = $defaultContext;
        } elseif ($merge) {
            Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($defaultContext, $subContext);
            $subContext = $defaultContext;
        }

        $content = $this->renderer->render(
            new Renderer\Template\View\HandlebarsView($name, $subContext),
        );

        return new Handlebars\SafeString($content);
    }
}
