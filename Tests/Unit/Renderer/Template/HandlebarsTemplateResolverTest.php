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
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1613727984);

        /* @phpstan-ignore argument.type */
        new Src\Renderer\Template\HandlebarsTemplateResolver($this->getTemplatePaths()->setTemplatePaths([null]));
    }

    #[Framework\Attributes\Test]
    public function constructorThrowsExceptionIfTemplateRootPathIsNotResolvable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1613728252);

        new Src\Renderer\Template\HandlebarsTemplateResolver($this->getTemplatePaths()->setTemplatePaths(['EXT:foo/baz']));
    }

    #[Framework\Attributes\Test]
    public function constructorThrowsExceptionIfFileExtensionHasInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1613727952);

        /* @phpstan-ignore argument.type */
        new Src\Renderer\Template\HandlebarsTemplateResolver($this->getTemplatePaths(), [true]);
    }

    #[Framework\Attributes\Test]
    public function constructorThrowsExceptionIfFileExtensionStartsWithADot(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1613727713);

        new Src\Renderer\Template\HandlebarsTemplateResolver($this->getTemplatePaths(), ['.foo']);
    }

    #[Framework\Attributes\Test]
    public function constructorThrowsExceptionIfFileExtensionIsInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1613727593);

        new Src\Renderer\Template\HandlebarsTemplateResolver($this->getTemplatePaths(), ['foo?!']);
    }

    #[Framework\Attributes\Test]
    public function getTemplateRootPathsReturnsTemplateRootPaths(): void
    {
        self::assertSame([$this->templateRootPath], $this->subject->getTemplateRootPaths());
    }

    #[Framework\Attributes\Test]
    public function setTemplateRootPathsAppliesSortedTemplateRootPaths(): void
    {
        $templateRootPaths = [
            20 => $this->templateRootPath . '/',
            10 => $this->templateRootPath . '/foo',
        ];
        $this->subject->setTemplateRootPaths($templateRootPaths);

        $expected = [
            $this->templateRootPath . '/foo',
            $this->templateRootPath,
        ];
        self::assertSame($expected, $this->subject->getTemplateRootPaths());
    }

    #[Framework\Attributes\Test]
    public function getSupportedFileExtensionsReturnsSupportedFileExtensions(): void
    {
        $this->subject->setSupportedFileExtensions(['hbs']);
        self::assertSame(['hbs'], $this->subject->getSupportedFileExtensions());
    }

    #[Framework\Attributes\Test]
    public function setSupportedFileExtensionsSetsDefaultFileExtensionsIfGivenFileExtensionsAreEmpty(): void
    {
        $this->subject->setSupportedFileExtensions([]);
        self::assertSame(
            Src\Renderer\Template\HandlebarsTemplateResolver::DEFAULT_FILE_EXTENSIONS,
            $this->subject->getSupportedFileExtensions(),
        );
    }

    #[Framework\Attributes\Test]
    public function supportsIndicatesSupportOfGivenFileExtensionByResolver(): void
    {
        self::assertFalse($this->subject->supports('jpeg'));
        self::assertTrue($this->subject->supports('hbs'));
        // File extensions are case-sensitive
        self::assertFalse($this->subject->supports('HBS'));
    }

    #[Framework\Attributes\Test]
    public function resolveTemplatePathResolvesRelateTemplatePathCorrectly(): void
    {
        $templatePath = 'DummyTemplate';
        $expected = $this->templateRootPath . '/DummyTemplate.hbs';

        self::assertSame($expected, $this->subject->resolveTemplatePath($templatePath));
    }

    #[Framework\Attributes\Test]
    public function resolveTemplatePathResolvesAbsoluteTemplatePathCorrectly(): void
    {
        $templatePath = \dirname(__DIR__, 2) . '/Fixtures/Templates/DummyTemplate.hbs';
        $expected = $this->templateRootPath . '/DummyTemplate.hbs';

        self::assertSame($expected, $this->subject->resolveTemplatePath($templatePath));
    }
}
