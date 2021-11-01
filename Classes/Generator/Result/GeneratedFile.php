<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2021 Elias Häußler <e.haeussler@familie-redlich.de>
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

namespace Fr\Typo3Handlebars\Generator\Result;

/**
 * GeneratedFile
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 *
 * @implements \ArrayAccess<string, mixed>
 */
final class GeneratedFile implements \ArrayAccess
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $type;

    /**
     * @var array<string, mixed>
     */
    private $additionalData;

    /**
     * @var bool
     */
    private $generated = true;

    /**
     * @var string|null
     */
    private $content;

    /**
     * @var string|null
     */
    private $previousContent;

    /**
     * @param string $filename
     * @param array<string, mixed> $additionalData
     */
    public function __construct(string $filename, string $type, array $additionalData = [])
    {
        $this->filename = $filename;
        $this->type = $type;
        $this->additionalData = $additionalData;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array<string, mixed>
     */
    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }

    public function isGenerated(): bool
    {
        return $this->generated;
    }

    public function setGenerated(bool $generated): self
    {
        $this->generated = $generated;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getPreviousContent(): ?string
    {
        return $this->previousContent;
    }

    public function setPreviousContent(?string $previousContent): self
    {
        $this->previousContent = $previousContent;
        return $this;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->additionalData[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->additionalData[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        $this->additionalData[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->additionalData[$offset]);
    }
}
