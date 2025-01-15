<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2024 Elias Häußler <e.haeussler@familie-redlich.de>
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

namespace Fr\Typo3Handlebars\Renderer\Helper;

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
        private Renderer\Renderer $renderer,
    ) {}

    public function render(Context\HelperContext $context): string
    {
        $name = $context[0];
        $arguments = $context->arguments;
        array_shift($arguments);

        // Custom context is optional
        $customContext = [];
        if ($arguments !== []) {
            $customContext = (array)array_pop($arguments);
        }

        // Create new handlebars layout item
        $fn = static fn(): string => $context->renderChildren() ?? '';
        $handlebarsLayout = new Renderer\Component\Layout\HandlebarsLayout($fn);

        // Add layout to layout stack
        $renderingContext = &$context->renderingContext;
        $renderingContext['_layoutStack'] ??= [];
        $renderingContext['_layoutStack'][] = $handlebarsLayout;

        // Merge data with supplied data
        $variables = array_replace_recursive($renderingContext, $customContext, $context->hash);

        // Render layout with merged data
        return $this->renderer->render(
            new Renderer\Template\View\HandlebarsView($name, $variables),
        );
    }
}
