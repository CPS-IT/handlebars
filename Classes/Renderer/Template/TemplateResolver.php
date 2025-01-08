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
 * TemplateResolver
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
interface TemplateResolver
{
    /**
     * Check whether given file extension is supported by this resolver.
     */
    public function supports(string $fileExtension): bool;

    /**
     * Resolve given partial path to the full partial path including the base partial path.
     *
     * @throws Exception\PartialPathIsNotResolvable
     */
    public function resolvePartialPath(string $partialPath): string;

    /**
     * Resolve given template path to the full template path including the base template path.
     *
     * @throws Exception\TemplatePathIsNotResolvable
     */
    public function resolveTemplatePath(string $templatePath): string;
}
