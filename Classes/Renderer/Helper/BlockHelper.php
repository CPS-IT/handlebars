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
    public function render(Handlebars\HelperOptions $options, string $name = ''): string
    {
        $actions = [];
        $layoutStack = Renderer\Component\Layout\HandlebarsLayoutStack::fromScope($options->scope);

        // Parse layouts and fetch all parsed layout actions for the requested block
        foreach ($layoutStack as $layout) {
            if (!$layout->isParsed()) {
                $layout->parse($options->scope);
            }

            foreach ($layout->getActions($name) as $action) {
                $actions[] = $action;
            }
        }

        try {
            $initial = $options->fn($options->scope);
        } catch (\Exception) {
            $initial = '';
        }

        // Walk through layout actions and apply them to the rendered block
        return array_reduce(
            $actions,
            static fn(string $value, Renderer\Component\Layout\HandlebarsLayoutAction $action): string => $action->render($value),
            $initial,
        );
    }
}
