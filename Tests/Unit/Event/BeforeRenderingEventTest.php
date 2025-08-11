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

namespace CPSIT\Typo3Handlebars\Tests\Unit\Event;

use CPSIT\Typo3Handlebars as Src;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * BeforeRenderingEventTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Event\BeforeRenderingEvent::class)]
final class BeforeRenderingEventTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Src\Event\BeforeRenderingEvent $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\Event\BeforeRenderingEvent(
            new Src\Renderer\Template\View\HandlebarsView('foo'),
            ['foo' => 'baz'],
            $this->createMock(Src\Renderer\HandlebarsRenderer::class),
        );
    }

    #[Framework\Attributes\Test]
    public function getViewReturnsHandlebarsView(): void
    {
        self::assertEquals(
            new Src\Renderer\Template\View\HandlebarsView('foo'),
            $this->subject->getView(),
        );
    }

    #[Framework\Attributes\Test]
    public function getVariablesReturnsVariables(): void
    {
        self::assertSame(['foo' => 'baz'], $this->subject->getVariables());
    }

    #[Framework\Attributes\Test]
    public function setVariablesModifiesVariables(): void
    {
        $this->subject->setVariables(['modified' => 'variables']);

        self::assertSame(['modified' => 'variables'], $this->subject->getVariables());
    }

    #[Framework\Attributes\Test]
    public function addVariableAddsSingleVariable(): void
    {
        $this->subject->addVariable('foo', 'boo');
        $this->subject->addVariable('baz', 'foo');

        self::assertSame(
            [
                'foo' => 'boo',
                'baz' => 'foo',
            ],
            $this->subject->getVariables(),
        );
    }

    #[Framework\Attributes\Test]
    public function removeVariableRemoveSingleVariable(): void
    {
        $this->subject->removeVariable('foo');

        self::assertSame([], $this->subject->getVariables());
    }

    #[Framework\Attributes\Test]
    public function getRendererReturnsRenderer(): void
    {
        self::assertInstanceOf(Src\Renderer\HandlebarsRenderer::class, $this->subject->getRenderer());
    }
}
