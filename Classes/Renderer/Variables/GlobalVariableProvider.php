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

namespace Fr\Typo3Handlebars\Renderer\Variables;

use Symfony\Component\DependencyInjection;

/**
 * GlobalVariableProvider
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final readonly class GlobalVariableProvider implements VariableProvider
{
    /**
     * @param array<string, mixed> $variables
     */
    public function __construct(
        #[DependencyInjection\Attribute\Autowire('%handlebars.variables%')]
        private array $variables,
    ) {}

    public function get(): array
    {
        return $this->variables;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->variables[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->variables[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): never
    {
        throw new \LogicException('Variables cannot be modified.', 1736274549);
    }

    public function offsetUnset(mixed $offset): never
    {
        throw new \LogicException('Variables cannot be modified.', 1736274551);
    }

    public static function getPriority(): int
    {
        return 0;
    }
}
