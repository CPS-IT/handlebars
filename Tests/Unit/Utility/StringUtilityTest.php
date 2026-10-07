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

namespace CPSIT\Typo3Handlebars\Tests\Unit\Utility;

use CPSIT\Typo3Handlebars as Src;
use DevTheorem\Handlebars;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * StringUtilityTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Utility\StringUtility::class)]
final class StringUtilityTest extends TestingFramework\Core\Unit\UnitTestCase
{
    /**
     * @return \Generator<string, array{mixed, bool}>
     */
    public static function isStringableChecksIfValueCanBeConvertedToStringDataProvider(): \Generator
    {
        yield 'string' => ['foo', true];
        yield 'SafeString' => [new Handlebars\SafeString('foo'), true];
        yield 'null' => [null, true];
        yield 'Stringable' => [
            new class implements \Stringable {
                public function __toString(): string
                {
                    return 'foo';
                }
            },
            true,
        ];
        yield 'bool' => [true, true];
        yield 'int' => [1, true];
        yield 'float' => [1.0, true];
        yield 'object' => [new \stdClass(), false];
        yield 'array' => [['foo' => 'bar'], false];
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('isStringableChecksIfValueCanBeConvertedToStringDataProvider')]
    public function isStringableChecksIfValueCanBeConvertedToString(mixed $value, bool $expected): void
    {
        self::assertSame($expected, Src\Utility\StringUtility::isStringable($value));
    }
}
