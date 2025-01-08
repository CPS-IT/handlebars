<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2025 Elias Häußler <e.haeussler@familie-redlich.de>
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

namespace Fr\Typo3Handlebars\Tests\Unit\Renderer\Variables;

use Fr\Typo3Handlebars as Src;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * VariableBagTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Variables\VariableBag::class)]
final class VariableBagTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Src\Renderer\Variables\VariableBag $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\Renderer\Variables\VariableBag([
            new Src\Renderer\Variables\GlobalVariableProvider([
                'foo' => 'boo',
                'baz' => 'foo',
            ]),
            new Src\Renderer\Variables\GlobalVariableProvider([
                'foo' => 'baz',
            ]),
        ]);
    }

    #[Framework\Attributes\Test]
    public function getReturnsMergedVariablesFromProviders(): void
    {
        $expected = [
            'foo' => 'boo',
            'baz' => 'foo',
        ];

        self::assertSame($expected, $this->subject->get());
    }

    #[Framework\Attributes\Test]
    public function objectCanBeAccessedAsReadOnlyArray(): void
    {
        // offsetExists
        self::assertTrue(isset($this->subject['foo']));
        self::assertTrue(isset($this->subject['baz']));
        self::assertFalse(isset($this->subject['boo']));

        // offsetGet
        self::assertSame('boo', $this->subject['foo']);
        self::assertSame('foo', $this->subject['baz']);
        self::assertNull($this->subject['boo']);
    }

    #[Framework\Attributes\Test]
    public function offsetSetThrowsLogicException(): void
    {
        $this->expectExceptionObject(
            new \LogicException('Variables cannot be modified.', 1736274871),
        );

        $this->subject['boo'] = 'baz';
    }

    #[Framework\Attributes\Test]
    public function offsetUnsetThrowsLogicException(): void
    {
        $this->expectExceptionObject(
            new \LogicException('Variables cannot be modified.', 1736274873),
        );

        unset($this->subject['foo']);
    }
}
