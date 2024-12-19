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

namespace Fr\Typo3Handlebars\Renderer\Helper\Context;

/**
 * RenderingContextStack
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 *
 * @implements \IteratorAggregate<int, array<string, mixed>>
 */
final class RenderingContextStack implements \IteratorAggregate
{
    /**
     * @param list<array<string, mixed>> $stack
     */
    public function __construct(
        private array &$stack,
    ) {
        $this->reset();
    }

    /**
     * @param array{null}|list<array<string, mixed>> $contexts
     */
    public static function fromRuntimeCall(array &$contexts): self
    {
        if ($contexts === [null]) {
            $stack = [];
        } else {
            $stack = &$contexts;
        }

        return new self($stack);
    }

    /**
     * @return array<string, mixed>|null
     *
     * @impure
     */
    public function pop(): ?array
    {
        $current = \current($this->stack);

        // Go to previous context in stack
        prev($this->stack);

        if ($current === false || !is_array($current)) {
            return null;
        }

        return $current;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function first(): ?array
    {
        $firstKey = \array_key_first($this->stack);

        if ($firstKey !== null) {
            return $this->stack[$firstKey];
        }

        return null;
    }

    /**
     * @impure
     */
    public function reset(): void
    {
        end($this->stack);
    }

    /**
     * @impure
     */
    public function isEmpty(): bool
    {
        return \current($this->stack) === false;
    }

    /**
     * @return \ArrayIterator<int, array<string, mixed>>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator(array_reverse($this->stack));
    }
}
