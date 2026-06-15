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

namespace CPSIT\Typo3Handlebars\Tests\Unit\Exception;

use CPSIT\Typo3Handlebars as Src;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * TypeIsNotSupportedTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Exception\TypeIsNotSupported::class)]
final class TypeIsNotSupportedTest extends TestingFramework\Core\Unit\UnitTestCase
{
    #[Framework\Attributes\Test]
    public function constructorReturnsExceptionForGivenType(): void
    {
        $actual = new Src\Exception\TypeIsNotSupported('string', null);

        self::assertSame(
            'The type "null" is not supported, it must be "string" instead.',
            $actual->getMessage(),
        );
        self::assertSame(1781515238, $actual->getCode());
    }

    #[Framework\Attributes\Test]
    public function constructorReturnsExceptionForGivenTypes(): void
    {
        $actual = new Src\Exception\TypeIsNotSupported(['string', 'array'], null);

        self::assertSame(
            'The type "null" is not supported, it must be one of "string", "array" instead.',
            $actual->getMessage(),
        );
        self::assertSame(1781515238, $actual->getCode());
    }
}
