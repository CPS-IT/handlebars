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
 * InvalidClassExceptionTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Exception\InvalidClassException::class)]
final class InvalidClassExceptionTest extends TestingFramework\Core\Unit\UnitTestCase
{
    #[Framework\Attributes\Test]
    public function createReturnsExceptionForGivenClassName(): void
    {
        $actual = Src\Exception\InvalidClassException::create(self::class);

        self::assertInstanceOf(Src\Exception\InvalidClassException::class, $actual);
        self::assertSame(\sprintf('The class "%s" does not exist.', self::class), $actual->getMessage());
        self::assertSame(1638182580, $actual->getCode());
    }

    #[Framework\Attributes\Test]
    public function forServiceReturnsExceptionForGivenServiceId(): void
    {
        $actual = Src\Exception\InvalidClassException::forService('foo');

        self::assertInstanceOf(Src\Exception\InvalidClassException::class, $actual);
        self::assertSame('Class name of service "foo" cannot be resolved or does not exist.', $actual->getMessage());
        self::assertSame(1638183576, $actual->getCode());
    }
}
