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

namespace Fr\Typo3Handlebars\Tests\Unit\Renderer\Template;

use Fr\Typo3Handlebars as Src;
use Fr\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * HandlebarsTemplateResolverTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Template\HandlebarsTemplateResolver::class)]
final class HandlebarsTemplateResolverTest extends TestingFramework\Core\Unit\UnitTestCase
{
    use Tests\HandlebarsTemplateResolverTrait;

    private Src\Renderer\Template\HandlebarsTemplateResolver $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $templateResolver = $this->getTemplateResolver();

        self::assertInstanceOf(Src\Renderer\Template\HandlebarsTemplateResolver::class, $templateResolver);

        $this->subject = $templateResolver;
    }

    #[Framework\Attributes\Test]
    public function constructorThrowsExceptionIfTemplateRootPathHasInvalidType(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\RootPathIsMalicious(null),
        );

        new Src\Renderer\Template\HandlebarsTemplateResolver(
            new Src\Renderer\Template\TemplatePaths([
                new Tests\Unit\Fixtures\Classes\Renderer\Template\Path\DummyPathProvider(
                    /* @phpstan-ignore argument.type */
                    [null],
                ),
            ]),
        );
    }

    #[Framework\Attributes\Test]
    public function constructorThrowsExceptionIfTemplateRootPathIsNotResolvable(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\RootPathIsNotResolvable('EXT:foo/baz'),
        );

        new Src\Renderer\Template\HandlebarsTemplateResolver(
            new Src\Renderer\Template\TemplatePaths([
                new Tests\Unit\Fixtures\Classes\Renderer\Template\Path\DummyPathProvider(
                    ['EXT:foo/baz'],
                ),
            ]),
        );
    }

    #[Framework\Attributes\Test]
    public function constructorThrowsExceptionIfFileExtensionHasInvalidType(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\FileExtensionIsMalicious(true),
        );

        /* @phpstan-ignore argument.type */
        new Src\Renderer\Template\HandlebarsTemplateResolver($this->getTemplatePaths(), [true]);
    }

    #[Framework\Attributes\Test]
    public function constructorThrowsExceptionIfFileExtensionIsInvalid(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\FileExtensionIsInvalid('foo?!'),
        );

        new Src\Renderer\Template\HandlebarsTemplateResolver($this->getTemplatePaths(), ['foo?!']);
    }

    #[Framework\Attributes\Test]
    public function resolvePartialPathThrowsExceptionIfPartialPathCannotBeResolved(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\PartialPathIsNotResolvable('foo'),
        );

        $this->subject->resolvePartialPath('foo');
    }

    #[Framework\Attributes\Test]
    public function resolvePartialPathResolvesRelativePartialPathCorrectly(): void
    {
        $expected = $this->partialRootPath . '/DummyPartial.hbs';

        self::assertSame($expected, $this->subject->resolvePartialPath('DummyPartial'));
    }

    #[Framework\Attributes\Test]
    public function resolvePartialPathResolvesAbsolutePartialPathCorrectly(): void
    {
        $templatePath = \dirname(__DIR__, 2) . '/Fixtures/Partials/DummyPartial.hbs';
        $expected = $this->partialRootPath . '/DummyPartial.hbs';

        self::assertSame($expected, $this->subject->resolvePartialPath($templatePath));
    }

    #[Framework\Attributes\Test]
    public function resolveTemplatePathThrowsExceptionIfTemplatePathCannotBeResolved(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\TemplatePathIsNotResolvable('foo'),
        );

        $this->subject->resolveTemplatePath('foo');
    }

    #[Framework\Attributes\Test]
    public function resolveTemplatePathResolvesRelativeTemplatePathCorrectly(): void
    {
        $expected = $this->templateRootPath . '/DummyTemplate.hbs';

        self::assertSame($expected, $this->subject->resolveTemplatePath('DummyTemplate'));
    }

    #[Framework\Attributes\Test]
    public function resolveTemplatePathResolvesAbsoluteTemplatePathCorrectly(): void
    {
        $templatePath = \dirname(__DIR__, 2) . '/Fixtures/Templates/DummyTemplate.hbs';
        $expected = $this->templateRootPath . '/DummyTemplate.hbs';

        self::assertSame($expected, $this->subject->resolveTemplatePath($templatePath));
    }
}
