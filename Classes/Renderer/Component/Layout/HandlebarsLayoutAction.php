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
