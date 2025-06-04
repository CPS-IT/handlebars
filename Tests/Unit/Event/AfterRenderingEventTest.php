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

namespace Fr\Typo3Handlebars\Tests\Unit\Event;

use Fr\Typo3Handlebars as Src;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * AfterRenderingEventTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Event\AfterRenderingEvent::class)]
final class AfterRenderingEventTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Src\Event\AfterRenderingEvent $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\Event\AfterRenderingEvent(
            new Src\Renderer\Template\View\HandlebarsView('foo'),
            'baz',
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
    public function getContentReturnsContent(): void
    {
        self::assertSame('baz', $this->subject->getContent());
    }

    #[Framework\Attributes\Test]
    public function setContentModifiesContent(): void
    {
        $this->subject->setContent('modified content');

        self::assertSame('modified content', $this->subject->getContent());
    }

    #[Framework\Attributes\Test]
    public function getRendererReturnsRenderer(): void
    {
        self::assertInstanceOf(Src\Renderer\HandlebarsRenderer::class, $this->subject->getRenderer());
    }
}
