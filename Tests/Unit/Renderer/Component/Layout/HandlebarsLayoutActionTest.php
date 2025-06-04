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

namespace Fr\Typo3Handlebars\Tests\Unit\Renderer\Component\Layout;

use DevTheorem\Handlebars;
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
    private Handlebars\HelperOptions $options;

    public function setUp(): void
    {
        parent::setUp();

        $renderingContext = [];
        $data = [];

        $this->options = new Handlebars\HelperOptions(
            'foo',
            [],
            static fn() => 'baz',
            static fn() => 'baz',
            0,
            $renderingContext,
            $data,
        );
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('renderReturnsProcessedValueDataProvider')]
    public function renderReturnsProcessedValue(
        Src\Renderer\Component\Layout\HandlebarsLayoutActionMode $mode,
        string $expected,
    ): void {
        $subject = new Src\Renderer\Component\Layout\HandlebarsLayoutAction('foo', $this->options, $mode);

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
