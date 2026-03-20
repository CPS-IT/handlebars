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

namespace CPSIT\Typo3Handlebars\Tests\Unit\Renderer\Component\Layout;

use CPSIT\Typo3Handlebars as Src;
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
    public function fromScopeThrowsExceptionIfGivenScopeIsInvalid(): void
    {
        $scope = 'foo';

        $this->expectExceptionObject(
            new Src\Exception\RenderScopeIsInvalid($scope),
        );

        Src\Renderer\Component\Layout\HandlebarsLayoutStack::fromScope($scope);
    }

    #[Framework\Attributes\Test]
    public function fromScopeThrowsExceptionIfAvailableScopeContainsUnsupportedStack(): void
    {
        $scope = [
            '_layoutActions' => 'foo',
        ];

        $this->expectExceptionObject(
            new Src\Exception\RenderScopeContainsUnsupportedLayoutStack(),
        );

        Src\Renderer\Component\Layout\HandlebarsLayoutStack::fromScope($scope);
    }

    #[Framework\Attributes\Test]
    public function fromScopeReturnsExistingStackFromGivenScope(): void
    {
        $stack = new Src\Renderer\Component\Layout\HandlebarsLayoutStack();
        $scope = [
            '_layoutActions' => $stack,
        ];

        self::assertSame($stack, Src\Renderer\Component\Layout\HandlebarsLayoutStack::fromScope($scope));
    }

    #[Framework\Attributes\Test]
    public function fromScopeAddsNewStackToScope(): void
    {
        $scope = [];

        $actual = Src\Renderer\Component\Layout\HandlebarsLayoutStack::fromScope($scope);

        self::assertSame(
            [
                '_layoutActions' => $actual,
            ],
            $scope,
        );
    }

    #[Framework\Attributes\Test]
    public function destroyIfEmptyDoesNothingIfStackWithinGivenScopeIsUnsupported(): void
    {
        $scope = [
            '_layoutActions' => 'foo',
        ];

        Src\Renderer\Component\Layout\HandlebarsLayoutStack::destroyIfEmpty($scope);

        self::assertIsArray($scope);
        self::assertSame('foo', $scope['_layoutActions']);
    }

    #[Framework\Attributes\Test]
    public function destroyIfEmptyDoesNothingIfStackWithinGivenScopeIsNotEmpty(): void
    {
        $stack = new Src\Renderer\Component\Layout\HandlebarsLayoutStack();
        $stack->push(new Src\Renderer\Component\Layout\HandlebarsLayout(static fn() => ''));

        $scope = [
            '_layoutActions' => $stack,
        ];

        Src\Renderer\Component\Layout\HandlebarsLayoutStack::destroyIfEmpty($scope);

        self::assertIsArray($scope);
        self::assertSame($stack, $scope['_layoutActions']);
    }

    #[Framework\Attributes\Test]
    public function destroyIfEmptyRemovesStackFromGivenScope(): void
    {
        $stack = new Src\Renderer\Component\Layout\HandlebarsLayoutStack();

        $scope = [
            '_layoutActions' => $stack,
        ];

        Src\Renderer\Component\Layout\HandlebarsLayoutStack::destroyIfEmpty($scope);

        self::assertSame([], $scope);
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

    #[Framework\Attributes\Test]
    public function subjectIsIterable(): void
    {
        $layout1 = new Src\Renderer\Component\Layout\HandlebarsLayout(static fn() => '');
        $layout2 = new Src\Renderer\Component\Layout\HandlebarsLayout(static fn() => '');

        $this->subject->push($layout1);
        $this->subject->push($layout2);

        self::assertSame(
            [$layout2, $layout1],
            iterator_to_array($this->subject),
        );
    }
}
