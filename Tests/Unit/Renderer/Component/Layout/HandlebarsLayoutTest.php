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

namespace Fr\Typo3Handlebars\Tests\Unit\Renderer\Component\Layout;

use DevTheorem\Handlebars;
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
        $renderingContext = [];
        $data = [];

        return new Src\Renderer\Component\Layout\HandlebarsLayoutAction(
            $name,
            new Handlebars\HelperOptions(
                'foo',
                [],
                static fn() => '',
                static fn() => '',
                0,
                $renderingContext,
                $data,
            ),
        );
    }
}
