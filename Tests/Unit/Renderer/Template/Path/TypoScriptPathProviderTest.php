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
use Fr\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * TypoScriptPathProviderTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Template\Path\TypoScriptPathProvider::class)]
final class TypoScriptPathProviderTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Tests\Unit\Fixtures\Classes\DummyConfigurationManager $configurationManager;
    private Src\Renderer\Template\Path\TypoScriptPathProvider $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->configurationManager = new Tests\Unit\Fixtures\Classes\DummyConfigurationManager();
        $this->subject = new Src\Renderer\Template\Path\TypoScriptPathProvider($this->configurationManager);
    }

    #[Framework\Attributes\Test]
    public function getPartialRootPathsReturnsPartialRootPaths(): void
    {
        $this->configurationManager->configuration = [
            'view' => [
                'partialRootPaths' => [
                    0 => 'partialFoo',
                    10 => 'partialBaz',
                ],
            ],
        ];

        $expected = [
            0 => 'partialFoo',
            10 => 'partialBaz',
        ];

        self::assertSame($expected, $this->subject->getPartialRootPaths());
    }

    #[Framework\Attributes\Test]
    public function getTemplateRootPathsReturnsTemplateRootPaths(): void
    {
        $this->configurationManager->configuration = [
            'view' => [
                'templateRootPaths' => [
                    0 => 'templateFoo',
                    10 => 'templateBaz',
                ],
            ],
        ];

        $expected = [
            0 => 'templateFoo',
            10 => 'templateBaz',
        ];

        self::assertSame($expected, $this->subject->getTemplateRootPaths());
    }
}
