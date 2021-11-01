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
 * GeneratorResult
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class GeneratorResult
{
    public const STATE_SUCCESSFUL = 'successful';
    public const STATE_INCOMPLETE = 'incomplete';
    public const STATE_FAILED = 'failed';

    /**
     * @var array<string, GeneratedFile>
     */
    private $files;

    /**
     * @param GeneratedFile[] $files
     */
    public function __construct(array $files = [])
    {
        $this->setFiles($files);
    }

    public function addFile(GeneratedFile $file): void
    {
        $this->files[$file->getType()] = $file;
    }

    /**
     * @return GeneratedFile[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    public function getByType(string $type): ?GeneratedFile
    {
        return $this->files[$type] ?? null;
    }

    /**
     * @param callable $filter
     * @return GeneratedFile[]
     */
    public function getByFilter(callable $filter): array
    {
        return array_filter($this->files, $filter);
    }

    /**
     * @param GeneratedFile[] $files
     * @return self
     */
    public function setFiles(array $files): self
    {
        $this->files = [];
        foreach ($files as $file) {
            $this->addFile($file);
        }

        return $this;
    }

    public function getState(): string
    {
        $generatedFiles = $this->getByFilter(function (GeneratedFile $file): bool {
            return $file->isGenerated();
        });

        if ([] === $generatedFiles) {
            return self::STATE_FAILED;
        }
        if (count($this->files) !== count($generatedFiles)) {
            return self::STATE_INCOMPLETE;
        }

        return self::STATE_SUCCESSFUL;
    }
}
