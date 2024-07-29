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

use Fr\Typo3Handlebars\Exception\InvalidClassException;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * InvalidClassExceptionTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class InvalidClassExceptionTest extends UnitTestCase
{
    /**
     * @test
     */
    public static function createReturnsExceptionForGivenClassName(): void
    {
        $actual = InvalidClassException::create(__CLASS__);

        self::assertInstanceOf(InvalidClassException::class, $actual);
        self::assertSame(\sprintf('The class "%s" does not exist.', __CLASS__), $actual->getMessage());
        self::assertSame(1638182580, $actual->getCode());
    }

    /**
     * @test
     */
    public static function forServiceReturnsExceptionForGivenServiceId(): void
    {
        $actual = InvalidClassException::forService('foo');

        self::assertInstanceOf(InvalidClassException::class, $actual);
        self::assertSame('Class name of service "foo" cannot be resolved or does not exist.', $actual->getMessage());
        self::assertSame(1638183576, $actual->getCode());
    }
}
