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

/**
 * TemplateResolverInterface
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
interface TemplateResolverInterface
{
    /**
     * Get list of file extensions supported by this resolver.
     *
     * @return string[] List of file extensions supported by this resolver
     */
    public function getSupportedFileExtensions(): array;

    /**
     * Check whether given file extension is supported by this resolver.
     *
     * @param string $fileExtension File extension to be checked
     * @return bool `true` if given file extension is supported by this resolver, `false` otherwise
     */
    public function supports(string $fileExtension): bool;

    /**
     * Resolve given template path to the full template path including the base template path.
     *
     * @param string $templatePath Main template path
     * @return string Fully resolved template file name
     * @throws TemplateNotFoundException if template could not be found
     */
    public function resolveTemplatePath(string $templatePath): string;
}
