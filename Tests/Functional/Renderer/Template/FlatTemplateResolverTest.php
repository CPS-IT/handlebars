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
        $this->allowAdditionalRootPaths();

        parent::setUp();

        $this->templateRootPath = 'EXT:test_extension/Resources/Templates/';
        $this->partialRootPath = 'EXT:test_extension/Resources/Partials/';
        $this->templateResolver = new Src\Renderer\Template\FlatTemplateResolver($this->getTemplatePaths());
    }

    #[Framework\Attributes\Test]
    public function constructorIgnoresSubsequentPartialsWithSameName(): void
    {
        $viewConfiguration = $this->getViewConfiguration();
        $viewConfiguration[Src\Renderer\Template\Path\PathProvider::PARTIALS][5] = 'EXT:test_extension/Resources/Partials2/';

        $subject = new Src\Renderer\Template\FlatTemplateResolver(
            new Src\Renderer\Template\TemplatePaths([
                new Src\Renderer\Template\Path\GlobalPathProvider($viewConfiguration),
            ]),
        );

        $expected = $this->instancePath . '/typo3conf/ext/test_extension/Resources/Partials2/foo.hbs';

        self::assertSame($expected, $subject->resolvePartialPath('@foo'));
    }

    #[Framework\Attributes\Test]
    public function constructorIgnoresSubsequentTemplatesWithSameName(): void
    {
        $viewConfiguration = $this->getViewConfiguration();
        $viewConfiguration[Src\Renderer\Template\Path\PathProvider::TEMPLATES][5] = 'EXT:test_extension/Resources/Templates2/';

        $subject = new Src\Renderer\Template\FlatTemplateResolver(
            new Src\Renderer\Template\TemplatePaths([
                new Src\Renderer\Template\Path\GlobalPathProvider($viewConfiguration),
            ]),
        );

        $expected = $this->instancePath . '/typo3conf/ext/test_extension/Resources/Templates2/foo.hbs';

        self::assertSame($expected, $subject->resolveTemplatePath('@foo'));
    }

    #[Framework\Attributes\Test]
    public function resolvePartialPathRespectsPartialVariant(): void
    {
        $expected = $this->instancePath . '/typo3conf/ext/test_extension/Resources/Partials/foo--variant.hbs';

        self::assertSame($expected, $this->getTemplateResolver()->resolvePartialPath('@foo--variant'));
    }

    #[Framework\Attributes\Test]
    public function resolvePartialPathReturnsBasePartialForNonExistingPartialVariant(): void
    {
        $expected = $this->instancePath . '/typo3conf/ext/test_extension/Resources/Partials/foo.hbs';

        self::assertSame($expected, $this->getTemplateResolver()->resolvePartialPath('@foo--non-existing-variant'));
    }

    #[Framework\Attributes\Test]
    public function resolvePartialPathFallsBackToDefaultResolverIfPartialPathDoesNotContainLeadingReferenceCharacter(): void
    {
        $expected = $this->instancePath . '/typo3conf/ext/test_extension/Resources/Partials/foo.hbs';

        // foo vs @foo
        self::assertSame($expected, $this->getTemplateResolver()->resolvePartialPath('foo'));
    }

    #[Framework\Attributes\Test]
    public function resolvePartialPathThrowsExceptionIfGivenFormatIsNotSupported(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\TemplateFormatIsNotSupported('foo'),
        );

        $this->getTemplateResolver()->resolvePartialPath('@foo', 'foo');
    }

    #[Framework\Attributes\Test]
    public function resolvePartialPathRespectsFormat(): void
    {
        $expected = $this->instancePath . '/typo3conf/ext/test_extension/Resources/Partials/foo.html';

        self::assertSame($expected, $this->getTemplateResolver()->resolvePartialPath('@foo', 'html'));
    }

    #[Framework\Attributes\Test]
    public function resolvePartialPathFallsBackToDefaultResolverIfPartialPathCannotBeResolved(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\PartialPathIsNotResolvable('@baz'),
        );

        $this->getTemplateResolver()->resolvePartialPath('@baz');
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

    #[Framework\Attributes\Test]
    public function resolveTemplatePathFallsBackToDefaultResolverIfTemplatePathDoesNotContainLeadingReferenceCharacter(): void
    {
        $expected = $this->instancePath . '/typo3conf/ext/test_extension/Resources/Templates/main-layout.hbs';

        // main-layout vs @main-layout
        self::assertSame($expected, $this->getTemplateResolver()->resolveTemplatePath('main-layout'));
    }

    #[Framework\Attributes\Test]
    public function resolveTemplatePathThrowsExceptionIfGivenFormatIsNotSupported(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\TemplateFormatIsNotSupported('foo'),
        );

        $this->getTemplateResolver()->resolveTemplatePath('@foo', 'foo');
    }

    #[Framework\Attributes\Test]
    public function resolveTemplatePathRespectsFormat(): void
    {
        $expected = $this->instancePath . '/typo3conf/ext/test_extension/Resources/Templates/foo.html';

        self::assertSame($expected, $this->getTemplateResolver()->resolveTemplatePath('@foo', 'html'));
    }

    #[Framework\Attributes\Test]
    public function resolveTemplatePathFallsBackToDefaultResolverIfTemplatePathCannotBeResolved(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\TemplatePathIsNotResolvable('@baz'),
        );

        $this->getTemplateResolver()->resolveTemplatePath('@baz');
    }
}
