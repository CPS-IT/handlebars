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

namespace Fr\Typo3Handlebars\Tests\Unit\Exception;

use Fr\Typo3Handlebars as Src;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * InvalidHelperExceptionTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Exception\InvalidHelperException::class)]
final class InvalidHelperExceptionTest extends TestingFramework\Core\Unit\UnitTestCase
{
    #[Framework\Attributes\Test]
    public function forFunctionReturnsExceptionForGivenFunction(): void
    {
        $actual = Src\Exception\InvalidHelperException::forFunction('foo');

        self::assertSame('The helper function "foo" is invalid.', $actual->getMessage());
        self::assertSame(1637339290, $actual->getCode());
    }

    #[Framework\Attributes\Test]
    public function forUnsupportedTypeReturnsExceptionForTypeOfGivenArgument(): void
    {
        $actual = Src\Exception\InvalidHelperException::forUnsupportedType(null);

        self::assertSame('Only callables, strings and arrays can be defined as helpers, "null" given.', $actual->getMessage());
        self::assertSame(1637339694, $actual->getCode());
    }

    #[Framework\Attributes\Test]
    public function forInvalidCallableReturnsExceptionForGivenCallable(): void
    {
        /* @phpstan-ignore argument.type */
        $actual = Src\Exception\InvalidHelperException::forInvalidCallable(['foo', null]);

        self::assertSame('The helper function with callable [string, null] is not valid.', $actual->getMessage());
        self::assertSame(1638180355, $actual->getCode());
    }
}
