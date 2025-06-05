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

use DevTheorem\Handlebars;
use Fr\Typo3Handlebars\Attribute;
use Fr\Typo3Handlebars\Renderer;

/**
 * BlockHelper
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @see https://github.com/shannonmoeller/handlebars-layouts#block-name
 */
#[Attribute\AsHelper('block')]
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
