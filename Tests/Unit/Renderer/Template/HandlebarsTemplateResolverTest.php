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

namespace CPSIT\Typo3Handlebars\Tests\Unit\Renderer\Template;

use CPSIT\Typo3Handlebars as Src;
use CPSIT\Typo3Handlebars\Tests;
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
    public function resolvePartialPathThrowsExceptionIfRootPathHasInvalidType(): void
    {
        $subject = new Src\Renderer\Template\HandlebarsTemplateResolver(
            new Src\Renderer\Template\TemplatePaths([
                new Tests\Unit\Fixtures\Classes\Renderer\Template\Path\DummyPathProvider(
                    [],
                    /* @phpstan-ignore argument.type */
                    [null],
                ),
            ]),
        );

        $this->expectExceptionObject(
            new Src\Exception\RootPathIsMalicious(null),
        );

        $subject->resolvePartialPath('foo');
    }

    #[Framework\Attributes\Test]
    public function resolvePartialPathThrowsExceptionIfRootPathIsNotResolvable(): void
    {
        $subject = new Src\Renderer\Template\HandlebarsTemplateResolver(
            new Src\Renderer\Template\TemplatePaths([
                new Tests\Unit\Fixtures\Classes\Renderer\Template\Path\DummyPathProvider(
                    [],
                    ['EXT:foo/baz'],
                ),
            ]),
        );

        $this->expectExceptionObject(
            new Src\Exception\RootPathIsNotResolvable('EXT:foo/baz'),
        );

        $subject->resolvePartialPath('foo');
    }

    #[Framework\Attributes\Test]
    public function resolvePartialPathThrowsExceptionIfGivenFormatIsNotSupported(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\TemplateFormatIsNotSupported('baz'),
        );

        $this->subject->resolvePartialPath('DummyPartial', 'baz');
    }

    #[Framework\Attributes\Test]
    public function resolvePartialPathRespectsFormat(): void
    {
        $expected = $this->partialRootPath . '/DummyPartial.html';

        self::assertSame($expected, $this->subject->resolvePartialPath('DummyPartial', 'html'));
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
        $templatePath = dirname(__DIR__, 2) . '/Fixtures/Partials/DummyPartial.hbs';
        $expected = $this->partialRootPath . '/DummyPartial.hbs';

        self::assertSame($expected, $this->subject->resolvePartialPath($templatePath));
    }

    #[Framework\Attributes\Test]
    public function resolveTemplatePathThrowsExceptionIfRootPathHasInvalidType(): void
    {
        $subject = new Src\Renderer\Template\HandlebarsTemplateResolver(
            new Src\Renderer\Template\TemplatePaths([
                new Tests\Unit\Fixtures\Classes\Renderer\Template\Path\DummyPathProvider(
                    /* @phpstan-ignore argument.type */
                    [null],
                ),
            ]),
        );

        $this->expectExceptionObject(
            new Src\Exception\RootPathIsMalicious(null),
        );

        $subject->resolveTemplatePath('foo');
    }

    #[Framework\Attributes\Test]
    public function resolveTemplatePathThrowsExceptionIfRootPathIsNotResolvable(): void
    {
        $subject = new Src\Renderer\Template\HandlebarsTemplateResolver(
            new Src\Renderer\Template\TemplatePaths([
                new Tests\Unit\Fixtures\Classes\Renderer\Template\Path\DummyPathProvider(
                    ['EXT:foo/baz'],
                ),
            ]),
        );

        $this->expectExceptionObject(
            new Src\Exception\RootPathIsNotResolvable('EXT:foo/baz'),
        );

        $subject->resolveTemplatePath('foo');
    }

    #[Framework\Attributes\Test]
    public function resolveTemplatePathThrowsExceptionIfGivenFormatIsNotSupported(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\TemplateFormatIsNotSupported('baz'),
        );

        $this->subject->resolveTemplatePath('DummyTemplate', 'baz');
    }

    #[Framework\Attributes\Test]
    public function resolveTemplatePathRespectsFormat(): void
    {
        $expected = $this->templateRootPath . '/DummyTemplate.html';

        self::assertSame($expected, $this->subject->resolveTemplatePath('DummyTemplate', 'html'));
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
        $templatePath = dirname(__DIR__, 2) . '/Fixtures/Templates/DummyTemplate.hbs';
        $expected = $this->templateRootPath . '/DummyTemplate.hbs';

        self::assertSame($expected, $this->subject->resolveTemplatePath($templatePath));
    }
}
