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

namespace Fr\Typo3Handlebars\Tests\Unit\Renderer\Component\Layout;

use Fr\Typo3Handlebars as Src;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * HandlebarsLayoutStackTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Component\Layout\HandlebarsLayoutStack::class)]
final class HandlebarsLayoutStackTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Src\Renderer\Component\Layout\HandlebarsLayoutStack $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\Renderer\Component\Layout\HandlebarsLayoutStack();
    }

    #[Framework\Attributes\Test]
    public function pushAddsGivenLayoutToStack(): void
    {
        $layout = new Src\Renderer\Component\Layout\HandlebarsLayout(static fn() => '');

        $this->subject->push($layout);

        self::assertFalse($this->subject->isEmpty());
        self::assertSame($layout, $this->subject->last());
    }

    #[Framework\Attributes\Test]
    public function popReturnsAndRemovesLastLayoutFromStack(): void
    {
        $layout1 = new Src\Renderer\Component\Layout\HandlebarsLayout(static fn() => '');
        $layout2 = new Src\Renderer\Component\Layout\HandlebarsLayout(static fn() => '');

        $this->subject->push($layout1);
        $this->subject->push($layout2);

        self::assertSame($layout2, $this->subject->pop());
        self::assertFalse($this->subject->isEmpty());
        self::assertSame($layout1, $this->subject->pop());
        self::assertTrue($this->subject->isEmpty());
    }

    #[Framework\Attributes\Test]
    public function popReturnsNullIfStackIsEmpty(): void
    {
        self::assertNull($this->subject->pop());
    }

    #[Framework\Attributes\Test]
    public function firstReturnsLeastRecentlyAddedLayoutFromStack(): void
    {
        $layout1 = new Src\Renderer\Component\Layout\HandlebarsLayout(static fn() => '');
        $layout2 = new Src\Renderer\Component\Layout\HandlebarsLayout(static fn() => '');

        $this->subject->push($layout1);
        $this->subject->push($layout2);

        self::assertSame($layout1, $this->subject->first());
    }

    #[Framework\Attributes\Test]
    public function firstReturnsNullIfStackIsEmpty(): void
    {
        self::assertNull($this->subject->first());
    }

    #[Framework\Attributes\Test]
    public function lastReturnsMostRecentlyAddedLayoutFromStack(): void
    {
        $layout1 = new Src\Renderer\Component\Layout\HandlebarsLayout(static fn() => '');
        $layout2 = new Src\Renderer\Component\Layout\HandlebarsLayout(static fn() => '');

        $this->subject->push($layout1);
        $this->subject->push($layout2);

        self::assertSame($layout2, $this->subject->last());
    }

    #[Framework\Attributes\Test]
    public function lastReturnsNullIfStackIsEmpty(): void
    {
        self::assertNull($this->subject->last());
    }

    #[Framework\Attributes\Test]
    public function allReturnsCompleteLayoutStack(): void
    {
        self::assertSame([], $this->subject->all());

        $layout1 = new Src\Renderer\Component\Layout\HandlebarsLayout(static fn() => '');
        $layout2 = new Src\Renderer\Component\Layout\HandlebarsLayout(static fn() => '');

        $this->subject->push($layout1);
        $this->subject->push($layout2);

        self::assertSame([$layout1, $layout2], $this->subject->all());
    }

    #[Framework\Attributes\Test]
    public function reverseReturnsCloneWithReversedLayoutsInStack(): void
    {
        $layout1 = new Src\Renderer\Component\Layout\HandlebarsLayout(static fn() => '');
        $layout2 = new Src\Renderer\Component\Layout\HandlebarsLayout(static fn() => '');

        $this->subject->push($layout1);
        $this->subject->push($layout2);

        $actual = $this->subject->reverse();

        self::assertNotSame($this->subject, $actual);
        self::assertSame([$layout1, $layout2], $this->subject->all());
        self::assertSame([$layout2, $layout1], $actual->all());
    }

    #[Framework\Attributes\Test]
    public function resetRemovesAllLayoutsFromStack(): void
    {
        $layout1 = new Src\Renderer\Component\Layout\HandlebarsLayout(static fn() => '');
        $layout2 = new Src\Renderer\Component\Layout\HandlebarsLayout(static fn() => '');

        $this->subject->push($layout1);
        $this->subject->push($layout2);

        $this->subject->reset();

        self::assertSame([], $this->subject->all());
    }

    #[Framework\Attributes\Test]
    public function isEmptyReturnsTrueIfNoLayoutsWereAddedToStack(): void
    {
        self::assertTrue($this->subject->isEmpty());

        $layout = new Src\Renderer\Component\Layout\HandlebarsLayout(static fn() => '');

        $this->subject->push($layout);

        self::assertFalse($this->subject->isEmpty());
    }
}
