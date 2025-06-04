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

namespace Fr\Typo3Handlebars\Renderer\Helper;

use DevTheorem\Handlebars\HelperOptions;
use Fr\Typo3Handlebars\Renderer;

/**
 * ExtendHelper
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @see https://github.com/shannonmoeller/handlebars-layouts#extend-partial-context-keyvalue-
 */
final readonly class ExtendHelper implements Helper
{
    public function __construct(
        private Renderer\Component\Layout\HandlebarsLayoutStack $layoutStack,
        private Renderer\Renderer $renderer,
    ) {}

    public function render(HelperOptions $options, string $name = '', mixed ...$arguments): string
    {
        // Custom context is optional
        $customContext = [];
        if ($arguments !== []) {
            $customContext = (array)array_pop($arguments);
        }

        // Create new handlebars layout item
        $handlebarsLayout = new Renderer\Component\Layout\HandlebarsLayout($options->fn);

        // Add layout to layout stack
        $this->layoutStack->push($handlebarsLayout);

        // Merge data with supplied data
        $variables = array_replace_recursive($options->scope, $customContext, $options->hash);

        // Render layout with merged data
        try {
            return $this->renderer->render(
                new Renderer\Template\View\HandlebarsView($name, $variables),
            );
        } finally {
            $this->layoutStack->pop();
        }
    }
}
