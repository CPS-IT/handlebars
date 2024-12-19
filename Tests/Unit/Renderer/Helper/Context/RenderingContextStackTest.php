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

namespace Fr\Typo3Handlebars\Tests\Unit\Renderer\Helper\Context;

use Fr\Typo3Handlebars as Src;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * RenderingContextStackTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Helper\Context\RenderingContextStack::class)]
final class RenderingContextStackTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Src\Renderer\Helper\Context\RenderingContextStack $subject;

    /**
     * @var list<array<string, mixed>>
     */
    private array $stack = [
        ['foo' => 'baz'],
        ['baz' => 'foo'],
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\Renderer\Helper\Context\RenderingContextStack($this->stack);
    }

    #[Framework\Attributes\Test]
    public function constructorStoresGivenStackByReference(): void
    {
        $this->stack[] = ['boo' => 'faz'];

        $expected = [
            ['boo' => 'faz'],
            ['baz' => 'foo'],
            ['foo' => 'baz'],
        ];

        self::assertSame($expected, \iterator_to_array($this->subject));
    }

    #[Framework\Attributes\Test]
    public function constructorSetsArrayPointerToEndOfGivenStack(): void
    {
        $subject = new Src\Renderer\Helper\Context\RenderingContextStack($this->stack);

        self::assertSame(['baz' => 'foo'], $subject->pop());
    }

    #[Framework\Attributes\Test]
    public function fromRuntimeCallReturnsObjectWithEmptyStackIfGivenContextsAreEmpty(): void
    {
        // This is a "special" syntax from LightnCandy
        $contexts = [null];

        $subject = Src\Renderer\Helper\Context\RenderingContextStack::fromRuntimeCall($contexts);

        self::assertSame([], \iterator_to_array($subject));
    }

    #[Framework\Attributes\Test]
    public function fromRuntimeCallReturnsObjectWithGivenContextsByReference(): void
    {
        $subject = Src\Renderer\Helper\Context\RenderingContextStack::fromRuntimeCall($this->stack);

        self::assertSame(
            [
                ['baz' => 'foo'],
                ['foo' => 'baz'],
            ],
            \iterator_to_array($subject),
        );

        unset($this->stack[0], $this->stack[1]);

        self::assertSame([], \iterator_to_array($subject));
    }

    #[Framework\Attributes\Test]
    public function popReturnsCurrentContextAndSetsInternalArrayPointerToPreviousContextInStack(): void
    {
        self::assertSame(['baz' => 'foo'], $this->subject->pop());
        self::assertSame(['foo' => 'baz'], $this->subject->pop());
        self::assertTrue($this->subject->isEmpty());
    }

    #[Framework\Attributes\Test]
    public function popReturnsNullIfStackIsEmpty(): void
    {
        $this->stack = [];

        $subject = new Src\Renderer\Helper\Context\RenderingContextStack($this->stack);

        self::assertNull($subject->pop());
    }

    #[Framework\Attributes\Test]
    public function popReturnsNullIfStackIsProcessed(): void
    {
        $this->subject->pop();
        $this->subject->pop();

        self::assertNull($this->subject->pop());
    }

    #[Framework\Attributes\Test]
    public function popReturnsNullIfContextIsInvalid(): void
    {
        $stack = ['foo'];
        $subject = new Src\Renderer\Helper\Context\RenderingContextStack($stack);

        self::assertNull($subject->pop());
    }

    #[Framework\Attributes\Test]
    public function firstReturnsFirstElementInStack(): void
    {
        self::assertSame(['foo' => 'baz'], $this->subject->first());
    }

    #[Framework\Attributes\Test]
    public function firstReturnsNullIfStackIsEmpty(): void
    {
        $stack = [];
        $subject = new Src\Renderer\Helper\Context\RenderingContextStack($stack);

        self::assertNull($subject->first());
    }

    #[Framework\Attributes\Test]
    public function resetSetsInternalArrayPointerToEndOfStack(): void
    {
        self::assertSame(['baz' => 'foo'], $this->subject->pop());
        self::assertSame(['foo' => 'baz'], $this->subject->pop());

        $this->subject->reset();

        self::assertSame(['baz' => 'foo'], $this->subject->pop());
    }
}
