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

use Fr\Typo3Handlebars\Attribute;
use Fr\Typo3Handlebars\Exception;
use Fr\Typo3Handlebars\Renderer;

/**
 * BlockHelper
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @see https://github.com/shannonmoeller/handlebars-layouts#block-name
 */
final readonly class BlockHelper implements HelperInterface
{
    /**
     * @param array<string, mixed> $options
     * @throws Exception\UnsupportedTypeException
     */
    #[Attribute\AsHelper('block')]
    public function evaluate(string $name, array $options): string
    {
        $data = $options['_this'];
        $actions = $data['_layoutActions'] ?? [];
        $stack = $data['_layoutStack'] ?? [];

        // Parse layouts and fetch all parsed layout actions for the requested block
        while (!empty($stack)) {
            /** @var Renderer\Component\Layout\HandlebarsLayout $layout */
            $layout = array_shift($stack);
            if (!$layout->isParsed()) {
                $layout->parse();
            }
            $actions = array_merge($actions, $layout->getActions($name));
        }

        // Walk through layout actions and apply them to the rendered block
        $fn = $options['fn'] ?? static fn() => '';

        return array_reduce(
            $actions,
            static fn(string $value, Renderer\Component\Layout\HandlebarsLayoutAction $action): string => $action->render($value),
            $fn($data),
        );
    }
}
