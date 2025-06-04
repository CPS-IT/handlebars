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

namespace Fr\Typo3Handlebars\Tests\Unit\Data\Response;

use Fr\Typo3Handlebars as Src;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * SimpleProviderResponseTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Data\Response\SimpleProviderResponse::class)]
final class SimpleProviderResponseTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Src\Data\Response\SimpleProviderResponse $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new Src\Data\Response\SimpleProviderResponse(['foo' => 'baz']);
    }

    #[Framework\Attributes\Test]
    public function objectCanBeAccessedAsArray(): void
    {
        // Testing offsetExists
        self::assertTrue(isset($this->subject['foo']));
        self::assertFalse(isset($this->subject['baz']));

        // Testing offsetGet
        self::assertSame('baz', $this->subject['foo']);
        self::assertNull($this->subject['baz'] ?? null);

        // Testing offsetSet
        $this->subject['baz'] = 'dummy';
        self::assertTrue(isset($this->subject['baz']));
        self::assertSame('dummy', $this->subject['baz']);

        // Testing offsetUnset
        unset($this->subject['baz']);
        self::assertFalse(isset($this->subject['baz']));
        self::assertNull($this->subject['baz'] ?? null);
    }

    #[Framework\Attributes\Test]
    public function toArrayReturnsObjectData(): void
    {
        self::assertSame(['foo' => 'baz'], $this->subject->toArray());
    }
}
