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

use DevTheorem\Handlebars;
use Fr\Typo3Handlebars\Renderer;

/**
 * BlockHelper
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @see https://github.com/shannonmoeller/handlebars-layouts#block-name
 */
final readonly class BlockHelper implements Helper
{
    public function __construct(
        private Renderer\Component\Layout\HandlebarsLayoutStack $layoutStack,
    ) {}

    public function render(Handlebars\HelperOptions $options, string $name = ''): string
    {
        $actions = [];
        $stack = $this->layoutStack->reverse();

        // Parse layouts and fetch all parsed layout actions for the requested block
        while (!$stack->isEmpty()) {
            $layout = $stack->pop();

            if (!$layout->isParsed()) {
                $layout->parse();
            }

            foreach ($layout->getActions($name) as $action) {
                $actions[] = $action;
            }
        }

        // Walk through layout actions and apply them to the rendered block
        return array_reduce(
            $actions,
            static fn(string $value, Renderer\Component\Layout\HandlebarsLayoutAction $action): string => $action->render($value),
            $options->fn($options->scope),
        );
    }
}
