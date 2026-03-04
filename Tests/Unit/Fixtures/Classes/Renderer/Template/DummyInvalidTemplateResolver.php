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

namespace CPSIT\Typo3Handlebars\Tests\Unit\Fixtures\Classes\Renderer\Template;

use CPSIT\Typo3Handlebars\Renderer;

/**
 * DummyInvalidTemplateResolver
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 */
final class DummyInvalidTemplateResolver extends Renderer\Template\BaseTemplateResolver
{
    /**
     * @var array<string, string>
     */
    public array $templateMap = [];

    public function resolvePartialPath(string $partialPath, ?string $format = null): string
    {
        return $this->templateMap[$partialPath] ?? '/foo.hbs';
    }

    public function resolveTemplatePath(string $templatePath, ?string $format = null): string
    {
        return $this->templateMap[$templatePath] ?? '/foo.hbs';
    }
}
