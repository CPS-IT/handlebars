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

namespace Fr\Typo3Handlebars\Tests\Unit\Renderer\Template\Path;

use Fr\Typo3Handlebars as Src;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * GlobalPathProviderTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Template\Path\GlobalPathProvider::class)]
final class GlobalPathProviderTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Src\Renderer\Template\Path\GlobalPathProvider $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\Renderer\Template\Path\GlobalPathProvider([
            Src\Renderer\Template\Path\PathProvider::PARTIALS => [
                0 => 'partialFoo',
                10 => 'partialBaz',
            ],
            Src\Renderer\Template\Path\PathProvider::TEMPLATES => [
                0 => 'templateFoo',
                10 => 'templateBaz',
            ],
        ]);
    }

    #[Framework\Attributes\Test]
    public function getPartialRootPathsReturnsPartialRootPaths(): void
    {
        $expected = [
            0 => 'partialFoo',
            10 => 'partialBaz',
        ];

        self::assertSame($expected, $this->subject->getPartialRootPaths());
    }

    #[Framework\Attributes\Test]
    public function getTemplateRootPathsReturnsTemplateRootPaths(): void
    {
        $expected = [
            0 => 'templateFoo',
            10 => 'templateBaz',
        ];

        self::assertSame($expected, $this->subject->getTemplateRootPaths());
    }
}
