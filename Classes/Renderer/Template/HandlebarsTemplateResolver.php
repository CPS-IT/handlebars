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

use Fr\Typo3Handlebars\Exception\TemplateNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * HandlebarsTemplateResolver
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class HandlebarsTemplateResolver implements TemplateResolverInterface
{
    public const DEFAULT_FILE_EXTENSIONS = ['hbs', 'handlebars', 'html'];

    /**
     * @var string[]
     */
    protected array $templateRootPaths = [];

    /**
     * @var string[]
     */
    protected array $supportedFileExtensions = [];

    /**
     * @param string[] $supportedFileExtensions
     */
    public function __construct(TemplatePaths $templateRootPaths, array $supportedFileExtensions = self::DEFAULT_FILE_EXTENSIONS)
    {
        $this->setTemplateRootPaths($templateRootPaths->get());
        $this->setSupportedFileExtensions($supportedFileExtensions);
    }

    /**
     * @return string[]
     */
    public function getTemplateRootPaths(): array
    {
        return $this->templateRootPaths;
    }

    /**
     * Set sorted list of template root paths.
     *
     * Sorts the given template root paths by array key and applies them to the resolver.
     * Additionally, trailing directory separators are stripped of to normalize paths and
     * allow less error-prone template path resolving.
     *
     * @param string[] $templateRootPaths List of (probably unsorted) template root paths
     * @return self This object to allow fluent access to this instance
     */
    public function setTemplateRootPaths(array $templateRootPaths): self
    {
        $this->templateRootPaths = [];
        ksort($templateRootPaths);
        foreach ($templateRootPaths as $templateRootPath) {
            $this->validateTemplateRootPath($templateRootPath);
            $this->templateRootPaths[] = rtrim($templateRootPath, DIRECTORY_SEPARATOR);
        }
        return $this;
    }

    public function getSupportedFileExtensions(): array
    {
        return $this->supportedFileExtensions;
    }

    /**
     * Set file extensions to be supported by this resolver.
     *
     * Applies the given list of supported file extensions to this resolver. In case the
     * given list is empty or contains only empty values, {@see DEFAULT_FILE_EXTENSIONS}
     * is applied instead.
     *
     * @param string[] $supportedFileExtensions List of file extensions supported by this resolver
     * @return self This object to allow fluent access to this instance
     */
    public function setSupportedFileExtensions(array $supportedFileExtensions): self
    {
        if ($supportedFileExtensions === []) {
            $supportedFileExtensions = self::DEFAULT_FILE_EXTENSIONS;
        }
        array_map([$this, 'validateFileExtension'], $supportedFileExtensions);
        $this->supportedFileExtensions = $supportedFileExtensions;
        return $this;
    }

    public function supports(string $fileExtension): bool
    {
        return \in_array($fileExtension, $this->supportedFileExtensions, true);
    }

    public function resolveTemplatePath(string $templatePath): string
    {
        $filename = $templatePath;
        foreach (array_reverse($this->templateRootPaths) as $templateRootPath) {
            foreach ($this->supportedFileExtensions as $extension) {
                $possibleFilename = $this->resolveFilename($templatePath, $templateRootPath, $extension);
                if (file_exists($possibleFilename)) {
                    return $possibleFilename;
                }
            }
        }
        if (file_exists($possibleFilename = $this->resolveFilename($filename))) {
            return $possibleFilename;
        }
        throw new TemplateNotFoundException($filename, 1606217089);
    }

    protected function resolveFilename(string $templatePath, string $templateRootPath = null, string $extension = null): string
    {
        if ($templateRootPath !== null) {
            $filename = $templateRootPath . DIRECTORY_SEPARATOR . ltrim($templatePath, DIRECTORY_SEPARATOR);
        } else {
            $filename = $templatePath;
        }
        if ($extension !== null && !\in_array(pathinfo($filename, PATHINFO_EXTENSION), $this->supportedFileExtensions)) {
            $filename .= '.' . $extension;
        }
        $filename = GeneralUtility::getFileAbsFileName($filename);
        return $filename;
    }

    protected function validateTemplateRootPath(mixed $templateRootPath): void
    {
        if (!\is_string($templateRootPath)) {
            throw new \InvalidArgumentException(
                \sprintf('Template root path must be of type string, "%s" given.', \get_debug_type($templateRootPath)),
                1613727984
            );
        }
        if (GeneralUtility::getFileAbsFileName($templateRootPath) === '') {
            throw new \InvalidArgumentException(
                \sprintf('Template root path must be resolvable by %s::getFileAbsFileName().', GeneralUtility::class),
                1613728252
            );
        }
    }

    protected function validateFileExtension(mixed $fileExtension): void
    {
        if (!\is_string($fileExtension)) {
            throw new \InvalidArgumentException(
                \sprintf('File extension must be of type string, "%s" given.', \get_debug_type($fileExtension)),
                1613727952
            );
        }
        if (str_starts_with(trim($fileExtension), '.')) {
            throw new \InvalidArgumentException('File extension must not start with a dot.', 1613727713);
        }
        if (preg_match('/^[\w\-.]+$/', $fileExtension) !== 1) {
            throw new \InvalidArgumentException(
                \sprintf('File extension "%s" is not valid.', $fileExtension),
                1613727593
            );
        }
    }
}
