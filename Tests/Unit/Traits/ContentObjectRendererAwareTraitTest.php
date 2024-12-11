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

use Fr\Typo3Handlebars as Src;
use Fr\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use TYPO3\CMS\Frontend;
use TYPO3\TestingFramework;

/**
 * ContentObjectRendererAwareTraitTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Traits\ContentObjectRendererAwareTrait::class)]
final class ContentObjectRendererAwareTraitTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Tests\Unit\Fixtures\Classes\Traits\DummyContentObjectRendererAwareTraitClass $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Tests\Unit\Fixtures\Classes\Traits\DummyContentObjectRendererAwareTraitClass();
    }

    #[Framework\Attributes\Test]
    public function setContentObjectRendererSetsContentObjectRenderer(): void
    {
        $contentObjectRenderer = new Frontend\ContentObject\ContentObjectRenderer();
        $this->subject->setContentObjectRenderer($contentObjectRenderer);

        self::assertSame($contentObjectRenderer, $this->subject->getContentObjectRenderer());
    }

    #[Framework\Attributes\Test]
    public function assertContentObjectRendererIsAvailableThrowsExceptionIfContentObjectRendererIsNotAvailable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1615813615);

        $this->subject->testAssertContentObjectRendererIsAvailable();
    }

    #[Framework\Attributes\Test]
    public function assertContentObjectRendererIsAvailableSucceedsIfContentObjectRendererIsAvailable(): void
    {
        $contentObjectRenderer = new Frontend\ContentObject\ContentObjectRenderer();
        $this->subject->setContentObjectRenderer($contentObjectRenderer);

        $this->subject->testAssertContentObjectRendererIsAvailable();

        self::assertSame($contentObjectRenderer, $this->subject->getContentObjectRenderer());
    }
}
