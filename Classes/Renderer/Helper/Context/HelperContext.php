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
 * HelperContext
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 *
 * @implements \ArrayAccess<int|string, mixed>
 */
final class HelperContext implements \ArrayAccess
{
    /**
     * @param list<mixed> $arguments
     * @param array<string, mixed> $hash
     * @param array<string, mixed> $renderingContext
     * @param array<'root'|int, array<string, mixed>> $data
     * @param callable|null $childrenClosure
     * @param callable|null $inverseClosure
     */
    public function __construct(
        public readonly array $arguments, // 1...n-1
        public readonly array $hash, // n['hash']
        public readonly RenderingContextStack $contextStack, // n['contexts']
        public array &$renderingContext, // n['_this']
        public array &$data, // n['data'] => 'root', ...
        private $childrenClosure = null, // n['fn']
        private $inverseClosure = null, // n['inverse']
    ) {}

    /**
     * @param list<mixed> $options
     */
    public static function fromRuntimeCall(array &$options): self
    {
        $context = array_pop($options);

        $arguments = $options;
        $hash = $context['hash'];
        $contextStack = RenderingContextStack::fromRuntimeCall($context['contexts']);
        $renderingContext = &$context['_this'];
        $data = &$context['data'];
        $childrenClosure = $context['fn'] ?? null;
        $inverseClosure = $context['inverse'] ?? null;

        return new self(
            $arguments,
            $hash,
            $contextStack,
            $renderingContext,
            $data,
            $childrenClosure,
            $inverseClosure,
        );
    }

    public function isBlockHelper(): bool
    {
        return $this->childrenClosure !== null;
    }

    public function renderChildren(): mixed
    {
        if ($this->childrenClosure === null) {
            return null;
        }

        return ($this->childrenClosure)(...\func_get_args());
    }

    public function renderInverse(): mixed
    {
        if ($this->inverseClosure === null) {
            return null;
        }

        return ($this->inverseClosure)(...\func_get_args());
    }

    public function offsetExists(mixed $offset): bool
    {
        if (is_numeric($offset)) {
            return isset($this->arguments[$offset]);
        }

        return isset($this->hash[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        if (is_numeric($offset)) {
            return $this->arguments[$offset]
                ?? throw new \OutOfBoundsException('Argument "' . $offset . '" does not exist.', 1736235839);
        }

        return $this->hash[$offset]
            ?? throw new \OutOfBoundsException('Hash "' . $offset . '" does not exist.', 1736235851);
    }

    public function offsetSet(mixed $offset, mixed $value): never
    {
        throw new \LogicException('Helper context is locked and cannot be modified.', 1734434746);
    }

    public function offsetUnset(mixed $offset): never
    {
        throw new \LogicException('Helper context is locked and cannot be modified.', 1734434780);
    }
}
