<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2025 Elias Häußler <e.haeussler@familie-redlich.de>
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

namespace Fr\Typo3Handlebars\Tests\Unit\Renderer\Component\Layout;

use Fr\Typo3Handlebars as Src;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * HandlebarsLayoutActionTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Component\Layout\HandlebarsLayoutAction::class)]
final class HandlebarsLayoutActionTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Src\Renderer\Helper\Context\HelperContext $context;

    public function setUp(): void
    {
        parent::setUp();

        $renderingContext = [];
        $data = [];
        $stack = [];

        $this->context = new Src\Renderer\Helper\Context\HelperContext(
            [],
            [],
            new Src\Renderer\Helper\Context\RenderingContextStack($stack),
            $renderingContext,
            $data,
            static fn() => 'baz'
        );
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('renderReturnsProcessedValueDataProvider')]
    public function renderReturnsProcessedValue(
        Src\Renderer\Component\Layout\HandlebarsLayoutActionMode $mode,
        string $expected,
    ): void {
        $subject = new Src\Renderer\Component\Layout\HandlebarsLayoutAction('foo', $this->context, $mode);

        self::assertSame($expected, $subject->render('foo'));
    }

    /**
     * @return \Generator<string, array{Src\Renderer\Component\Layout\HandlebarsLayoutActionMode, string}>
     */
    public static function renderReturnsProcessedValueDataProvider(): \Generator
    {
        yield 'append' => [Src\Renderer\Component\Layout\HandlebarsLayoutActionMode::Append, 'foobaz'];
        yield 'prepend' => [Src\Renderer\Component\Layout\HandlebarsLayoutActionMode::Prepend, 'bazfoo'];
        yield 'replace' => [Src\Renderer\Component\Layout\HandlebarsLayoutActionMode::Replace, 'baz'];
    }
}
