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

        self::assertInstanceOf(Src\Exception\InvalidHelperException::class, $actual);
        self::assertSame('The helper function "foo" is invalid.', $actual->getMessage());
        self::assertSame(1637339290, $actual->getCode());
    }

    #[Framework\Attributes\Test]
    public function forUnsupportedTypeReturnsExceptionForTypeOfGivenArgument(): void
    {
        $actual = Src\Exception\InvalidHelperException::forUnsupportedType(null);

        self::assertInstanceOf(Src\Exception\InvalidHelperException::class, $actual);
        self::assertSame('Only callables, strings and arrays can be defined as helpers, "null" given.', $actual->getMessage());
        self::assertSame(1637339694, $actual->getCode());
    }

    #[Framework\Attributes\Test]
    public function forInvalidCallableReturnsExceptionForGivenCallable(): void
    {
        /* @phpstan-ignore argument.type */
        $actual = Src\Exception\InvalidHelperException::forInvalidCallable(['foo', null]);

        self::assertInstanceOf(Src\Exception\InvalidHelperException::class, $actual);
        self::assertSame('The helper function with callable [string, null] is not valid.', $actual->getMessage());
        self::assertSame(1638180355, $actual->getCode());
    }
}
