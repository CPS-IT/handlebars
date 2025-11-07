<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace CPSIT\Typo3Handlebars\Renderer\Template;

use CPSIT\Typo3Handlebars\Exception;

/**
 * HandlebarsTemplateResolver
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class HandlebarsTemplateResolver extends BaseTemplateResolver
{
    /**
     * @var array<string, list<string>>
     */
    private array $resolvedPaths = [];

    /**
     * @param string[] $supportedFileExtensions
     */
    public function __construct(
        private readonly TemplatePaths $templatePaths,
        array $supportedFileExtensions = self::DEFAULT_FILE_EXTENSIONS,
    ) {
        $this->supportedFileExtensions = $this->resolveSupportedFileExtensions($supportedFileExtensions);
    }

    /**
     * @throws Exception\PartialPathIsNotResolvable
     * @throws Exception\RootPathIsMalicious
     * @throws Exception\RootPathIsNotResolvable
     * @throws Exception\TemplateFormatIsNotSupported
     */
    public function resolvePartialPath(string $partialPath, ?string $format = null): string
    {
        return $this->resolvePath($partialPath, $this->resolvePartialRootPaths(), $format)
            ?? throw new Exception\PartialPathIsNotResolvable($partialPath, $format);
    }

    /**
     * @throws Exception\RootPathIsMalicious
     * @throws Exception\RootPathIsNotResolvable
     * @throws Exception\TemplateFormatIsNotSupported
     * @throws Exception\TemplatePathIsNotResolvable
     */
    public function resolveTemplatePath(string $templatePath, ?string $format = null): string
    {
        return $this->resolvePath($templatePath, $this->resolveTemplateRootPaths(), $format)
            ?? throw new Exception\TemplatePathIsNotResolvable($templatePath, $format);
    }

    /**
     * @return list<string>
     * @throws Exception\RootPathIsMalicious
     * @throws Exception\RootPathIsNotResolvable
     */
    private function resolvePartialRootPaths(): array
    {
        $partialRootPaths = $this->templatePaths->getPartialRootPaths();

        return $this->resolveRootPathsFromCache($partialRootPaths);
    }

    /**
     * @return list<string>
     * @throws Exception\RootPathIsMalicious
     * @throws Exception\RootPathIsNotResolvable
     */
    private function resolveTemplateRootPaths(): array
    {
        $templateRootPaths = $this->templatePaths->getTemplateRootPaths();

        return $this->resolveRootPathsFromCache($templateRootPaths);
    }

    /**
     * @param array<int, string> $rootPaths
     * @return list<string>
     * @throws Exception\RootPathIsMalicious
     * @throws Exception\RootPathIsNotResolvable
     */
    private function resolveRootPathsFromCache(array $rootPaths): array
    {
        $hash = \sha1((string)\json_encode($rootPaths));

        return $this->resolvedPaths[$hash] ?? ($this->resolvedPaths[$hash] = $this->normalizeRootPaths($rootPaths));
    }

    /**
     * @param list<string> $rootPaths
     * @throws Exception\TemplateFormatIsNotSupported
     */
    private function resolvePath(string $path, array $rootPaths, ?string $format = null): ?string
    {
        $fileExtensions = $this->supportedFileExtensions;
        $filename = $path;

        if ($format !== null) {
            // Throw exception if given format is not supported
            if (!in_array($format, $fileExtensions, true)) {
                throw new Exception\TemplateFormatIsNotSupported($format);
            }

            $fileExtensions = [$format];
        }

        foreach (array_reverse($rootPaths) as $rootPath) {
            foreach ($fileExtensions as $extension) {
                $possibleFilename = $this->resolveFilename($path, $rootPath, $extension);

                if (is_file($possibleFilename)) {
                    return $possibleFilename;
                }
            }
        }

        if ($format === null && is_file($possibleFilename = $this->resolveFilename($filename))) {
            return $possibleFilename;
        }

        return null;
    }
}
