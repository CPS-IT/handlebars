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
     * @throws Exception\TemplateFormatIsNotSupported
     */
    public function resolvePartialPath(string $partialPath, ?string $format = null): string;

    /**
     * Resolve given template path to the full template path including the base template path.
     *
     * @throws Exception\TemplateFormatIsNotSupported
     * @throws Exception\TemplatePathIsNotResolvable
     */
    public function resolveTemplatePath(string $templatePath, ?string $format = null): string;
}
