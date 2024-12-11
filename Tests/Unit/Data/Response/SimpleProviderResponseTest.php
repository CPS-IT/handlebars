<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2020 Elias Häußler <e.haeussler@familie-redlich.de>
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
        self::assertNull($this->subject['baz']);

        // Testing offsetSet
        $this->subject['baz'] = 'dummy';
        self::assertTrue(isset($this->subject['baz']));
        self::assertSame('dummy', $this->subject['baz']);

        // Testing offsetUnset
        unset($this->subject['baz']);
        self::assertFalse(isset($this->subject['baz']));
        self::assertNull($this->subject['baz']);
    }

    #[Framework\Attributes\Test]
    public function toArrayReturnsObjectData(): void
    {
        self::assertSame(['foo' => 'baz'], $this->subject->toArray());
    }
}
