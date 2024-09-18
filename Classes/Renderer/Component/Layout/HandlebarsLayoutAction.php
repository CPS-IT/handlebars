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

use Fr\Typo3Handlebars\Exception;

/**
 * HandlebarsLayoutAction
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class HandlebarsLayoutAction
{
    public const REPLACE = 'replace';
    public const APPEND = 'append';
    public const PREPEND = 'prepend';

    protected readonly string $mode;

    /**
     * @param array<string, mixed> $data
     * @param callable $renderFunction
     * @throws Exception\UnsupportedTypeException
     */
    public function __construct(
        protected readonly array $data,
        protected $renderFunction,
        string $mode = self::REPLACE,
    ) {
        $this->mode = strtolower($mode);
        $this->validate();
    }

    /**
     * @param string $value
     * @return string
     * @throws Exception\UnsupportedTypeException
     */
    public function render(string $value): string
    {
        $renderResult = ($this->renderFunction)($this->data);

        return match ($this->mode) {
            self::APPEND => $value . $renderResult,
            self::PREPEND => $renderResult . $value,
            self::REPLACE => $renderResult,
            default => throw Exception\UnsupportedTypeException::create($this->mode),
        };
    }

    /**
     * @return string[]
     */
    protected function getSupportedModes(): array
    {
        return [
            self::REPLACE,
            self::APPEND,
            self::PREPEND,
        ];
    }

    /**
     * @throws Exception\UnsupportedTypeException
     */
    protected function validate(): void
    {
        if (!\in_array($this->mode, $this->getSupportedModes(), true)) {
            throw Exception\UnsupportedTypeException::create($this->mode);
        }
    }
}
