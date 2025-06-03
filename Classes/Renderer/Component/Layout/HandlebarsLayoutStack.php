<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2025 Elias Häußler <e.haeussler@familie-redlich.de>
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

use TYPO3\CMS\Core;

/**
 * HandlebarsLayoutStack
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class HandlebarsLayoutStack implements Core\SingletonInterface
{
    /**
     * @var HandlebarsLayout[]
     */
    private array $stack = [];

    public function push(HandlebarsLayout $layout): self
    {
        $this->stack[] = $layout;

        return $this;
    }

    public function pop(): ?HandlebarsLayout
    {
        return array_pop($this->stack);
    }

    public function first(): ?HandlebarsLayout
    {
        $first = reset($this->stack);

        if ($first === false) {
            return null;
        }

        return $first;
    }

    public function last(): ?HandlebarsLayout
    {
        $last = end($this->stack);

        if ($last === false) {
            return null;
        }

        return $last;
    }

    /**
     * @return HandlebarsLayout[]
     */
    public function all(): array
    {
        return $this->stack;
    }

    public function reverse(): self
    {
        $clone = clone $this;
        $clone->stack = array_reverse($this->stack);

        return $clone;
    }

    public function reset(): void
    {
        $this->stack = [];
    }

    /**
     * @phpstan-assert-if-false HandlebarsLayout $this->pop()
     * @phpstan-assert-if-false HandlebarsLayout $this->first()
     * @phpstan-assert-if-false HandlebarsLayout $this->last()
     */
    public function isEmpty(): bool
    {
        return $this->stack === [];
    }
}
