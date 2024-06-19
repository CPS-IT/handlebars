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

use Fr\Typo3Handlebars\Event\BeforeRenderingEvent;
use Fr\Typo3Handlebars\Renderer\HandlebarsRenderer;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * BeforeRenderingEventTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class BeforeRenderingEventTest extends UnitTestCase
{
    /**
     * @var BeforeRenderingEvent
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new BeforeRenderingEvent('foo', ['foo' => 'baz'], $this->createMock(HandlebarsRenderer::class));
    }

    /**
     * @test
     */
    public function getTemplatePathReturnsTemplatePath(): void
    {
        self::assertSame('foo', $this->subject->getTemplatePath());
    }

    /**
     * @test
     */
    public function getDataReturnsData(): void
    {
        self::assertSame(['foo' => 'baz'], $this->subject->getData());
    }

    /**
     * @test
     */
    public function setDataModifiesData(): void
    {
        $this->subject->setData(['modified' => 'data']);

        self::assertSame(['modified' => 'data'], $this->subject->getData());
    }

    /**
     * @test
     */
    public function getRendererReturnsRenderer(): void
    {
        self::assertInstanceOf(HandlebarsRenderer::class, $this->subject->getRenderer());
    }
}
