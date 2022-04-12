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

namespace Fr\Typo3Handlebars\Generator;

use Fr\Typo3Handlebars\Generator\Resolver\ClassResolver;
use Fr\Typo3Handlebars\Generator\Result\GeneratedFile;
use Fr\Typo3Handlebars\Generator\Writer\PhpWriter;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * FileGenerationTrait
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
trait FileGenerationTrait
{
    /**
     * @var PhpWriter
     */
    protected $phpWriter;

    /**
     * @var ClassResolver
     */
    protected $classResolver;

    /**
     * @param array{namespace: string, className: string} $classParts
     * @param array<string, mixed> $classDefinition
     * @return array{string, string, string|bool, string|null}
     */
    protected function generateClass(
        string $extensionKey,
        array $classParts,
        array $classDefinition = [],
        bool $overwriteExistingFile = false
    ): array {
        // Resolve PHP class
        $namespace = $classParts['namespace'];
        $className = $classParts['className'];
        $finalClassName = $namespace . '\\' . $className;
        $classFilename = $this->classResolver->locateClass($extensionKey, $finalClassName);

        // Define class definition
        $classDefinition = array_merge($classDefinition, [
            'name' => $className,
            'namespacename' => $namespace,
        ]);

        $result = [
            $finalClassName,
            $classFilename,
        ];

        // Generate PHP class
        if (file_exists($classFilename) && $overwriteExistingFile) {
            $previousContent = file_get_contents($classFilename) ?: '';
            $result[] = $this->phpWriter->write($classFilename, $classDefinition);
            $result[] = $previousContent;
        } elseif (!file_exists($classFilename)) {
            $result[] = $this->phpWriter->write($classFilename, $classDefinition);
            $result[] = null;
        } else {
            $result[] = $this->phpWriter->fill($classDefinition);
            $result[] = null;
        }

        return $result;
    }

    protected function restoreGeneratedFile(GeneratedFile $file): void
    {
        unlink($file->getFilename());
        $file->setGenerated(false);

        if (null !== $file->getPreviousContent()) {
            GeneralUtility::writeFile($file->getFilename(), $file->getPreviousContent());
        }
    }
}
