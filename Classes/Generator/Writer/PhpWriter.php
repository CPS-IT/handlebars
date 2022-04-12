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

namespace Fr\Typo3Handlebars\Generator\Writer;

use Laminas\Code\DeclareStatement;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\FileGenerator;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * PhpWriter
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 */
class PhpWriter
{
    /**
     * @param array<string, mixed> $classDefinition
     */
    public function write(string $targetFile, array $classDefinition): bool
    {
        GeneralUtility::mkdir_deep(\dirname($targetFile));

        return GeneralUtility::writeFile($targetFile, $this->fill($classDefinition));
    }

    /**
     * @param array<string, mixed> $classDefinition
     */
    public function fill(array $classDefinition): string
    {
        return FileGenerator::fromArray([
            'class' => ClassGenerator::fromArray($classDefinition),
            'declares' => [
                DeclareStatement::STRICT_TYPES => 1,
            ],
        ])->generate();
    }
}
