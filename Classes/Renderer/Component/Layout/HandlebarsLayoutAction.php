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

namespace Fr\Typo3Handlebars\Renderer\Component\Layout;

use DevTheorem\Handlebars;

/**
 * HandlebarsLayoutAction
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final readonly class HandlebarsLayoutAction
{
    public function __construct(
        private string $name,
        private Handlebars\HelperOptions $context,
        private HandlebarsLayoutActionMode $mode = HandlebarsLayoutActionMode::Replace,
    ) {}

    public function render(string $value): string
    {
        $renderResult = $this->context->fn($this->context->scope);

        return match ($this->mode) {
            HandlebarsLayoutActionMode::Append => $value . $renderResult,
            HandlebarsLayoutActionMode::Prepend => $renderResult . $value,
            HandlebarsLayoutActionMode::Replace => $renderResult,
        };
    }

    public function getName(): string
    {
        return $this->name;
    }
}
