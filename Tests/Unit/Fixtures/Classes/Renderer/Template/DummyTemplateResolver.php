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

namespace Fr\Typo3Handlebars\Tests\Unit\Fixtures\Classes\Renderer\Template;

use Fr\Typo3Handlebars\Renderer;
use Fr\Typo3Handlebars\Renderer\Template\TemplatePaths;
use org\bovigo\vfs;

/**
 * DummyTemplateResolver
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class DummyTemplateResolver extends Renderer\Template\BaseTemplateResolver
{
    private readonly vfs\vfsStreamDirectory $root;

    public function __construct()
    {
        $this->root = vfs\vfsStream::setup(uniqid('ext_handlebars_test_'));
    }

    public function resolvePartialPath(string $partialPath): string
    {
        return vfs\vfsStream::newFile($partialPath, 0000)->at($this->root)->url();
    }

    public function resolveTemplatePath(string $templatePath): string
    {
        return vfs\vfsStream::newFile($templatePath, 0000)->at($this->root)->url();
    }

    public function resolveFilename(string $path, ?string $rootPath = null, ?string $extension = null): string
    {
        return parent::resolveFilename($path, $rootPath, $extension);
    }

    public function resolveTemplatePaths(TemplatePaths $templatePaths): array
    {
        return parent::resolveTemplatePaths($templatePaths);
    }

    public function resolveSupportedFileExtensions(array $supportedFileExtensions): array
    {
        return parent::resolveSupportedFileExtensions($supportedFileExtensions);
    }
}
