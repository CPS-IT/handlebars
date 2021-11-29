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

use Fr\Typo3Handlebars\Exception\InvalidHelperException;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * InvalidHelperExceptionTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class InvalidHelperExceptionTest extends UnitTestCase
{
    /**
     * @test
     */
    public function forFunctionReturnsExceptionForGivenFunction(): void
    {
        $actual = InvalidHelperException::forFunction('foo');

        self::assertInstanceOf(InvalidHelperException::class, $actual);
        self::assertSame('The helper function "foo" is invalid.', $actual->getMessage());
        self::assertSame(1637339290, $actual->getCode());
    }

    /**
     * @test
     */
    public function forUnsupportedTypeReturnsExceptionForTypeOfGivenArgument(): void
    {
        $actual = InvalidHelperException::forUnsupportedType(null);

        self::assertInstanceOf(InvalidHelperException::class, $actual);
        self::assertSame('Only callables, strings and arrays can be defined as helpers, "NULL" given.', $actual->getMessage());
        self::assertSame(1637339694, $actual->getCode());
    }

    /**
     * @test
     */
    public function forInvalidCallableReturnsExceptionForGivenCallable(): void
    {
        /* @phpstan-ignore-next-line */
        $actual = InvalidHelperException::forInvalidCallable(['foo', null]);

        self::assertInstanceOf(InvalidHelperException::class, $actual);
        self::assertSame('The helper function with callable [string, NULL] is not valid.', $actual->getMessage());
        self::assertSame(1638180355, $actual->getCode());
    }
}
