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

namespace Fr\Typo3Handlebars\Renderer\Template;

use Fr\Typo3Handlebars\Exception;

/**
 * HandlebarsTemplateResolver
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class HandlebarsTemplateResolver extends BaseTemplateResolver
{
    /**
     * @param string[] $supportedFileExtensions
     * @throws Exception\RootPathIsMalicious
     * @throws Exception\RootPathIsNotResolvable
     */
    public function __construct(
        TemplatePaths $templatePaths,
        array $supportedFileExtensions = self::DEFAULT_FILE_EXTENSIONS,
    ) {
        [$this->templateRootPaths, $this->partialRootPaths] = $this->resolveTemplatePaths($templatePaths);
        $this->supportedFileExtensions = $this->resolveSupportedFileExtensions($supportedFileExtensions);
    }

    public function resolvePartialPath(string $partialPath): string
    {
        return $this->resolvePath($partialPath, $this->partialRootPaths)
            ?? throw new Exception\PartialPathIsNotResolvable($partialPath);
    }

    public function resolveTemplatePath(string $templatePath): string
    {
        return $this->resolvePath($templatePath, $this->templateRootPaths)
            ?? throw new Exception\TemplatePathIsNotResolvable($templatePath);
    }

    /**
     * @param list<string> $rootPaths
     */
    private function resolvePath(string $path, array $rootPaths): ?string
    {
        $filename = $path;

        foreach (array_reverse($rootPaths) as $rootPath) {
            foreach ($this->supportedFileExtensions as $extension) {
                $possibleFilename = $this->resolveFilename($path, $rootPath, $extension);
                if (is_file($possibleFilename)) {
                    return $possibleFilename;
                }
            }
        }

        if (is_file($possibleFilename = $this->resolveFilename($filename))) {
            return $possibleFilename;
        }

        return null;
    }
}
