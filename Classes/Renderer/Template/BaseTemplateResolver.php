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

namespace Fr\Typo3Handlebars\Renderer\Template;

use Fr\Typo3Handlebars\Exception;
use TYPO3\CMS\Core;

/**
 * BaseTemplateResolver
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
abstract class BaseTemplateResolver implements TemplateResolver
{
    protected const DEFAULT_FILE_EXTENSIONS = ['hbs', 'handlebars', 'html'];

    /**
     * @var list<string>
     */
    protected array $partialRootPaths = [];

    /**
     * @var list<string>
     */
    protected array $templateRootPaths = [];

    /**
     * @var list<string>
     */
    protected array $supportedFileExtensions = self::DEFAULT_FILE_EXTENSIONS;

    public function supports(string $fileExtension): bool
    {
        return \in_array($fileExtension, $this->supportedFileExtensions, true);
    }

    protected function resolveFilename(string $path, ?string $rootPath = null, ?string $extension = null): string
    {
        if ($rootPath !== null) {
            $filename = $rootPath . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
        } else {
            $filename = $path;
        }

        if ($extension !== null && !$this->supports(pathinfo($filename, PATHINFO_EXTENSION))) {
            $filename .= '.' . $extension;
        }

        $resolvedFilename = Core\Utility\GeneralUtility::getFileAbsFileName($filename);

        if ($resolvedFilename === '' && Core\Utility\PathUtility::isAllowedAdditionalPath($filename)) {
            return $filename;
        }

        return $resolvedFilename;
    }

    /**
     * @return array{list<string>, list<string>}
     * @throws Exception\RootPathIsMalicious
     * @throws Exception\RootPathIsNotResolvable
     */
    protected function resolveTemplatePaths(TemplatePaths $templatePaths): array
    {
        return [
            $this->normalizeRootPaths($templatePaths->getTemplateRootPaths()),
            $this->normalizeRootPaths($templatePaths->getPartialRootPaths()),
        ];
    }

    /**
     * @param string[] $rootPaths
     * @return list<string>
     * @throws Exception\RootPathIsMalicious
     * @throws Exception\RootPathIsNotResolvable
     */
    protected function normalizeRootPaths(array $rootPaths): array
    {
        $normalizedRootPaths = [];

        ksort($rootPaths);

        foreach ($rootPaths as $rootPath) {
            if (!\is_string($rootPath)) {
                throw new Exception\RootPathIsMalicious($rootPath);
            }

            $normalizedRootPath = rtrim($rootPath, DIRECTORY_SEPARATOR);
            $normalizedRootPath = Core\Utility\GeneralUtility::getFileAbsFileName($normalizedRootPath);

            if ($normalizedRootPath === '') {
                if (!Core\Utility\PathUtility::isAllowedAdditionalPath($rootPath)) {
                    throw new Exception\RootPathIsNotResolvable($rootPath);
                }

                $normalizedRootPath = $rootPath;
            }

            $normalizedRootPaths[] = $normalizedRootPath;
        }

        return $normalizedRootPaths;
    }

    /**
     * @param string[] $supportedFileExtensions
     * @return list<string>
     */
    protected function resolveSupportedFileExtensions(array $supportedFileExtensions): array
    {
        if ($supportedFileExtensions === []) {
            $supportedFileExtensions = self::DEFAULT_FILE_EXTENSIONS;
        }

        return array_values(
            \array_unique(
                array_map($this->normalizeFileExtension(...), $supportedFileExtensions),
            ),
        );
    }

    /**
     * @throws Exception\FileExtensionIsInvalid
     * @throws Exception\FileExtensionIsMalicious
     */
    protected function normalizeFileExtension(mixed $fileExtension): string
    {
        if (!\is_string($fileExtension)) {
            throw new Exception\FileExtensionIsMalicious($fileExtension);
        }
        if (preg_match('/^[\w\-.]+$/', $fileExtension) !== 1) {
            throw new Exception\FileExtensionIsInvalid($fileExtension);
        }

        return ltrim(trim($fileExtension), '.');
    }
}
