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
use Fr\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * ViewIsNotSupportedTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Exception\ViewIsNotSupported::class)]
final class ViewIsNotSupportedTest extends TestingFramework\Core\Unit\UnitTestCase
{
    #[Framework\Attributes\Test]
    public function createReturnsExceptionForGivenType(): void
    {
        $actual = new Src\Exception\ViewIsNotSupported(new Tests\Unit\Fixtures\Classes\DummyView());

        self::assertSame(
            sprintf(
                'The given view "%s" cannot be used to render Handlebars templates.',
                Tests\Unit\Fixtures\Classes\DummyView::class,
            ),
            $actual->getMessage(),
        );
        self::assertSame(1740478079, $actual->getCode());
    }
}
