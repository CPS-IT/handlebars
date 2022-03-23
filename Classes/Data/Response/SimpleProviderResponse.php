<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2020 Elias Häußler <e.haeussler@familie-redlich.de>
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

namespace Fr\Typo3Handlebars\Data\Response;

/**
 * SimpleProviderResponse
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 *
 * @implements \ArrayAccess<string, mixed>
 */
class SimpleProviderResponse implements ProviderResponseInterface, \ArrayAccess
{
    /**
     * @var array<string, mixed>
     */
    protected $data;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function offsetExists($offset): bool
    {
        return \array_key_exists($offset, $this->data);
    }

    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->data[$offset];
        }
        return null;
    }

    public function offsetSet($offset, $value): void
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
