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

namespace Fr\Typo3Handlebars\Renderer\Template;

use Fr\Typo3Handlebars\Exception;
use Symfony\Component\Finder;

/**
 * FlatTemplateResolver
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @see https://fractal.build/guide/core-concepts/naming.html
 */
final class FlatTemplateResolver extends BaseTemplateResolver
{
    private const MAX_FILE_DEPTH = 30;
    private const VARIANT_SEPARATOR = '--';

    private readonly HandlebarsTemplateResolver $fallbackResolver;

    /**
     * @var array<string, Finder\SplFileInfo>
     */
    private readonly array $flattenedPartials;

    /**
     * @var array<string, Finder\SplFileInfo>
     */
    private readonly array $flattenedTemplates;

    /**
     * @param list<string> $supportedFileExtensions
     * @throws Exception\RootPathIsMalicious
     * @throws Exception\RootPathIsNotResolvable
     */
    public function __construct(
        TemplatePaths $templatePaths,
        array $supportedFileExtensions = self::DEFAULT_FILE_EXTENSIONS,
    ) {
        $this->fallbackResolver = new HandlebarsTemplateResolver($templatePaths, $supportedFileExtensions);
        [$this->templateRootPaths, $this->partialRootPaths] = $this->resolveTemplatePaths($templatePaths);
        $this->supportedFileExtensions = $this->resolveSupportedFileExtensions($supportedFileExtensions);
        $this->flattenedPartials = $this->buildPathMap($this->partialRootPaths);
        $this->flattenedTemplates = $this->buildPathMap($this->templateRootPaths);
    }

    public function resolvePartialPath(string $partialPath, ?string $format = null): string
    {
        return $this->resolvePath($partialPath, $this->flattenedPartials, $format)
            ?? $this->fallbackResolver->resolvePartialPath($partialPath, $format);
    }

    public function resolveTemplatePath(string $templatePath, ?string $format = null): string
    {
        return $this->resolvePath($templatePath, $this->flattenedTemplates, $format)
            ?? $this->fallbackResolver->resolveTemplatePath($templatePath, $format);
    }

    /**
     * @param array<string, Finder\SplFileInfo> $flattenedFiles
     * @throws Exception\TemplateFormatIsNotSupported
     */
    private function resolvePath(string $path, array $flattenedFiles, ?string $format = null): ?string
    {
        // Throw exception if given format is not supported
        if ($format !== null && !\in_array($format, $this->supportedFileExtensions, true)) {
            throw new Exception\TemplateFormatIsNotSupported($format);
        }

        // Use default path resolving if path is not prefixed by "@"
        if (!str_starts_with($path, '@')) {
            return null;
        }

        // Append format if requested
        if ($format !== null) {
            $path .= '.' . $format;
        }

        // Strip "@" prefix from given template path
        $templateName = ltrim($path, '@');

        // Return filename if template exists
        if (isset($flattenedFiles[$templateName])) {
            return $flattenedFiles[$templateName]->getPathname();
        }

        // Strip off template variant
        if (str_contains($templateName, self::VARIANT_SEPARATOR)) {
            [$templateName] = explode(self::VARIANT_SEPARATOR, $templateName, 2);

            if (isset($flattenedFiles[$templateName])) {
                return $flattenedFiles[$templateName]->getPathname();
            }
        }

        return null;
    }

    /**
     * @param list<string> $rootPaths
     * @return array<string, Finder\SplFileInfo>
     */
    private function buildPathMap(array $rootPaths): array
    {
        $flattenedPaths = [];

        // Instantiate finder
        $finder = new Finder\Finder();
        $finder->files();
        $finder->name([...$this->buildExtensionPatterns()]);
        $finder->depth(sprintf('< %d', self::MAX_FILE_DEPTH));

        // Explicitly sort files and directories by name in order to streamline ordering
        // with logic used in Fractal to ensure that the first occurrence of a flattened
        // file is always used instead of relying on random behavior,
        // see https://fractal.build/guide/core-concepts/naming.html#uniqueness
        $finder->sortByName();

        // Build template map
        foreach (array_reverse($rootPaths) as $rootPath) {
            $path = $this->resolveFilename($rootPath);
            $pathFinder = clone $finder;
            $pathFinder->in($path);

            foreach ($pathFinder as $file) {
                if ($this->isFirstOccurrenceInRootPaths($flattenedPaths, $file)) {
                    $flattenedPaths[$this->resolveFlatFilename($file)] = $file;
                }

                if ($this->isFirstOccurrenceInRootPaths($flattenedPaths, $file, true)) {
                    $flattenedPaths[$this->resolveFlatFilename($file, true)] = $file;
                }
            }
        }

        return $flattenedPaths;
    }

    /**
     * @param array<string, Finder\SplFileInfo> $flattenedPaths
     */
    private function isFirstOccurrenceInRootPaths(
        array $flattenedPaths,
        Finder\SplFileInfo $file,
        bool $withExtension = false,
    ): bool {
        $filename = $this->resolveFlatFilename($file, $withExtension);

        // Early return if template is not registered yet
        if (!isset($flattenedPaths[$filename])) {
            return true;
        }

        // In order to streamline template file flattening with logic used in Fractal,
        // we always use the first flat file occurrence as resolved template, but provide
        // the option to override exactly this file within other template root paths.
        return $flattenedPaths[$filename]->getRelativePathname() === $file->getRelativePathname();
    }

    private function resolveFlatFilename(Finder\SplFileInfo $file, bool $withExtension = false): string
    {
        $pathname = $file->getPathname();
        $filename = pathinfo($pathname, PATHINFO_FILENAME);

        if ($withExtension) {
            $filename = $filename . '.' . pathinfo($pathname, PATHINFO_EXTENSION);
        }

        return $filename;
    }

    /**
     * @return \Generator<string>
     */
    private function buildExtensionPatterns(): \Generator
    {
        foreach ($this->supportedFileExtensions as $extension) {
            yield sprintf('*.%s', $extension);
        }
    }
}
