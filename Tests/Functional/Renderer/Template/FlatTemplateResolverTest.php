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

namespace Fr\Typo3Handlebars\Tests\Functional\Renderer\Template;

use Fr\Typo3Handlebars as Src;
use Fr\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * FlatTemplateResolverTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Template\FlatTemplateResolver::class)]
final class FlatTemplateResolverTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\HandlebarsTemplateResolverTrait;

    protected array $testExtensionsToLoad = [
        'test_extension',
    ];

    protected bool $initializeDatabase = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->templateRootPath = 'EXT:test_extension/Resources/Templates/';
        $this->templateResolver = new Src\Renderer\Template\FlatTemplateResolver($this->getTemplatePaths());
    }

    #[Framework\Attributes\Test]
    public function resolveTemplatePathRespectsTemplateVariant(): void
    {
        $expected = $this->instancePath . '/typo3conf/ext/test_extension/Resources/Templates/main-layout--variant.hbs';

        self::assertSame($expected, $this->getTemplateResolver()->resolveTemplatePath('@main-layout--variant'));
    }

    #[Framework\Attributes\Test]
    public function resolveTemplatePathReturnsBaseTemplateForNonExistingTemplateVariant(): void
    {
        $expected = $this->instancePath . '/typo3conf/ext/test_extension/Resources/Templates/main-layout.hbs';

        self::assertSame($expected, $this->getTemplateResolver()->resolveTemplatePath('@main-layout--non-existing-variant'));
    }
}
