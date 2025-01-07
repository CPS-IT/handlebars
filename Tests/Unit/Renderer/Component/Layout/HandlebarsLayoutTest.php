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
 * HandlebarsLayoutTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Component\Layout\HandlebarsLayout::class)]
final class HandlebarsLayoutTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Src\Renderer\Component\Layout\HandlebarsLayout $subject;

    private bool $parseFunctionInvoked = false;

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\Renderer\Component\Layout\HandlebarsLayout(
            fn() => $this->parseFunctionInvoked = true,
        );
    }

    #[Framework\Attributes\Test]
    public function parseInvokesParseFunctionAndMarksComponentAsParsed(): void
    {
        self::assertFalse($this->parseFunctionInvoked);
        self::assertFalse($this->subject->isParsed());

        $this->subject->parse();

        self::assertTrue($this->parseFunctionInvoked);
        self::assertTrue($this->subject->isParsed());
    }

    #[Framework\Attributes\Test]
    public function addActionRegistersGivenAction(): void
    {
        self::assertSame([], $this->subject->getActions());

        $action = $this->createAction('foo');

        $this->subject->addAction($action);

        self::assertSame(
            [
                'foo' => [
                    $action,
                ],
            ],
            $this->subject->getActions(),
        );
    }

    #[Framework\Attributes\Test]
    public function getActionsReturnsAllRegisteredActions(): void
    {
        $this->subject->addAction($this->createAction('foo'));
        $this->subject->addAction($this->createAction('baz'));

        self::assertSame(['foo', 'baz'], \array_keys($this->subject->getActions()));
    }

    #[Framework\Attributes\Test]
    public function getActionsReturnsRegisteredActionsByGivenName(): void
    {
        $fooAction = $this->createAction('foo');
        $bazAction = $this->createAction('baz');

        $this->subject->addAction($fooAction);
        $this->subject->addAction($bazAction);

        self::assertSame([$fooAction], $this->subject->getActions('foo'));
        self::assertSame([], $this->subject->getActions('missing'));
    }

    #[Framework\Attributes\Test]
    public function hasActionReturnsTrueIfActionOfGivenNameWasRegistered(): void
    {
        self::assertFalse($this->subject->hasAction('foo'));

        $this->subject->addAction($this->createAction('foo'));

        self::assertTrue($this->subject->hasAction('foo'));
    }

    private function createAction(string $name): Src\Renderer\Component\Layout\HandlebarsLayoutAction
    {
        $stack = [];
        $renderingContext = [];
        $data = [];

        return new Src\Renderer\Component\Layout\HandlebarsLayoutAction(
            $name,
            new Src\Renderer\Helper\Context\HelperContext(
                [],
                [],
                new Src\Renderer\Helper\Context\RenderingContextStack($stack),
                $renderingContext,
                $data,
            ),
        );
    }
}
