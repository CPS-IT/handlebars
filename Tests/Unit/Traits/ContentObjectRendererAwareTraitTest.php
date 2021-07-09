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

namespace Fr\Typo3Handlebars\Tests\Unit\Traits;

use Fr\Typo3Handlebars\Tests\Unit\Fixtures\Classes\Traits\DummyContentObjectRendererAwareTraitClass;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * ContentObjectRendererAwareTraitTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class ContentObjectRendererAwareTraitTest extends UnitTestCase
{
    /**
     * @var DummyContentObjectRendererAwareTraitClass
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new DummyContentObjectRendererAwareTraitClass();
    }

    /**
     * @test
     */
    public function setContentObjectRendererSetsContentObjectRenderer(): void
    {
        $contentObjectRenderer = new ContentObjectRenderer();
        $this->subject->setContentObjectRenderer($contentObjectRenderer);

        self::assertSame($contentObjectRenderer, $this->subject->getContentObjectRenderer());
    }

    /**
     * @test
     */
    public function assertContentObjectRendererIsAvailableThrowsExceptionIfContentObjectRendererIsNotAvailable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1615813615);

        $this->subject->testAssertContentObjectRendererIsAvailable();
    }

    /**
     * @test
     */
    public function assertContentObjectRendererIsAvailableSucceedsIfContentObjectRendererIsAvailable(): void
    {
        $contentObjectRenderer = new ContentObjectRenderer();
        $this->subject->setContentObjectRenderer($contentObjectRenderer);

        $this->subject->testAssertContentObjectRendererIsAvailable();
    }
}
