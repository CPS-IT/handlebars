<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2021 Elias Häußler <e.haeussler@familie-redlich.de>
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
            'foo',
            'baz',
            $this->createMock(Src\Renderer\HandlebarsRenderer::class),
        );
    }

    #[Framework\Attributes\Test]
    public function getTemplatePathReturnsTemplatePath(): void
    {
        self::assertSame('foo', $this->subject->getTemplatePath());
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
