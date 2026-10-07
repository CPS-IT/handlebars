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

namespace CPSIT\Typo3Handlebars\Tests\Unit\DataProcessing\DataSource;

use CPSIT\Typo3Handlebars as Src;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * CurrentValueTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\DataProcessing\DataSource\CurrentValue::class)]
final class CurrentValueTest extends TestingFramework\Core\Unit\UnitTestCase
{
    /**
     * @return \Generator<string, array{mixed}>
     */
    public static function wrapDoesNotWrapStringableValueDataProvider(): \Generator
    {
        yield 'string' => ['foo'];
        yield 'integer' => [42];
        yield 'float' => [1.5];
        yield 'boolean' => [false];
        yield 'null' => [null];
        yield 'stringable object' => [
            new class implements \Stringable {
                public function __toString(): string
                {
                    return 'foo';
                }
            },
        ];
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('wrapDoesNotWrapStringableValueDataProvider')]
    public function wrapDoesNotWrapStringableValue(mixed $value): void
    {
        self::assertSame($value, Src\DataProcessing\DataSource\CurrentValue::wrap($value));
    }

    /**
     * @return \Generator<string, array{mixed}>
     */
    public static function wrapWrapsNonStringableValueDataProvider(): \Generator
    {
        yield 'array' => [['foo' => 'bar']];
        yield 'object' => [new \stdClass()];
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('wrapWrapsNonStringableValueDataProvider')]
    public function wrapWrapsNonStringableValue(mixed $value): void
    {
        $actual = Src\DataProcessing\DataSource\CurrentValue::wrap($value);

        self::assertInstanceOf(Src\DataProcessing\DataSource\CurrentValue::class, $actual);
        self::assertSame($value, $actual->value);
        self::assertSame('', (string)$actual);
    }

    #[Framework\Attributes\Test]
    public function toStringReturnsStringRepresentationOfStringableValue(): void
    {
        $subject = new Src\DataProcessing\DataSource\CurrentValue(42);

        self::assertSame('42', (string)$subject);
    }
}
