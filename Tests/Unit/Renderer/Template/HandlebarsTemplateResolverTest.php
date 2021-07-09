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

use Fr\Typo3Handlebars\Renderer\Template\HandlebarsTemplateResolver;
use Fr\Typo3Handlebars\Tests\Unit\HandlebarsTemplateResolverTrait;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * HandlebarsTemplateResolverTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class HandlebarsTemplateResolverTest extends UnitTestCase
{
    use HandlebarsTemplateResolverTrait;

    /**
     * @var HandlebarsTemplateResolver
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        /** @phpstan-ignore-next-line subject */
        $this->subject = $this->getTemplateResolver();
    }

    /**
     * @test
     */
    public function constructorThrowsExceptionIfTemplateRootPathHasInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1613727984);

        /** @phpstan-ignore-next-line */
        new HandlebarsTemplateResolver([null]);
    }

    /**
     * @test
     */
    public function constructorThrowsExceptionIfTemplateRootPathIsNotResolvable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1613728252);

        new HandlebarsTemplateResolver(['EXT:foo/baz']);
    }

    /**
     * @test
     */
    public function constructorThrowsExceptionIfFileExtensionHasInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1613727952);

        /** @phpstan-ignore-next-line */
        new HandlebarsTemplateResolver([$this->getTemplateRootPath()], [true]);
    }

    /**
     * @test
     */
    public function constructorThrowsExceptionIfFileExtensionStartsWithADot(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1613727713);

        new HandlebarsTemplateResolver([$this->getTemplateRootPath()], ['.foo']);
    }

    /**
     * @test
     */
    public function constructorThrowsExceptionIfFileExtensionIsInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1613727593);

        new HandlebarsTemplateResolver([$this->getTemplateRootPath()], ['foo?!']);
    }

    /**
     * @test
     */
    public function getTemplateRootPathsReturnsTemplateRootPaths(): void
    {
        self::assertSame([$this->getTemplateRootPath()], $this->subject->getTemplateRootPaths());
    }

    /**
     * @test
     */
    public function setTemplateRootPathsAppliesSortedTemplateRootPaths(): void
    {
        $templateRootPaths = [
            20 => $this->getTemplateRootPath() . '/',
            10 => $this->getTemplateRootPath() . '/foo',
        ];
        $this->subject->setTemplateRootPaths($templateRootPaths);

        $expected = [
            $this->getTemplateRootPath() . '/foo',
            $this->getTemplateRootPath(),
        ];
        self::assertSame($expected, $this->subject->getTemplateRootPaths());
    }

    /**
     * @test
     */
    public function getSupportedFileExtensionsReturnsSupportedFileExtensions(): void
    {
        $this->subject->setSupportedFileExtensions(['hbs']);
        self::assertSame(['hbs'], $this->subject->getSupportedFileExtensions());
    }

    /**
     * @test
     */
    public function setSupportedFileExtensionsSetsDefaultFileExtensionsIfGivenFileExtensionsAreEmpty(): void
    {
        $this->subject->setSupportedFileExtensions([]);
        self::assertSame(
            HandlebarsTemplateResolver::DEFAULT_FILE_EXTENSIONS,
            $this->subject->getSupportedFileExtensions()
        );
    }

    /**
     * @test
     */
    public function supportsIndicatesSupportOfGivenFileExtensionByResolver(): void
    {
        self::assertFalse($this->subject->supports('jpeg'));
        self::assertTrue($this->subject->supports('hbs'));
        // File extensions are case-sensitive
        self::assertFalse($this->subject->supports('HBS'));
    }

    /**
     * @test
     */
    public function resolveTemplatePathResolvesRelateTemplatePathCorrectly(): void
    {
        $templatePath = 'DummyTemplate';
        $expected = $this->getTemplateRootPath() . '/DummyTemplate.hbs';

        self::assertSame($expected, $this->subject->resolveTemplatePath($templatePath));
    }

    /**
     * @test
     */
    public function resolveTemplatePathResolvesAbsoluteTemplatePathCorrectly(): void
    {
        $templatePath = 'EXT:handlebars/Tests/Unit/Fixtures/Templates/DummyTemplate.hbs';
        $expected = $this->getTemplateRootPath() . '/DummyTemplate.hbs';

        self::assertSame($expected, $this->subject->resolveTemplatePath($templatePath));
    }
}
