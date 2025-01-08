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

namespace Fr\Typo3Handlebars\Tests\Unit\Renderer\Template;

use Fr\Typo3Handlebars as Src;
use Fr\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * TemplatePathsTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Template\TemplatePaths::class)]
final class TemplatePathsTest extends TestingFramework\Core\Unit\UnitTestCase
{
    use Tests\HandlebarsTemplateResolverTrait;

    private Tests\Unit\Fixtures\Classes\Renderer\Template\Path\DummyPathProvider $pathProvider;
    private Src\Renderer\Template\TemplatePaths $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pathProvider = new Tests\Unit\Fixtures\Classes\Renderer\Template\Path\DummyPathProvider();
        $this->subject = new Src\Renderer\Template\TemplatePaths([
            $this->pathProvider,
            new Src\Renderer\Template\Path\GlobalPathProvider($this->getViewConfiguration()),
        ]);
    }

    /**
     * @param array<int, string> $templateRootPaths
     * @param array<int, string> $expected
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('getTemplateRootPathsMergesConfigurationFromPathProvidersDataProvider')]
    public function getTemplateRootPathsMergesConfigurationFromPathProviders(
        array $templateRootPaths,
        array $expected,
    ): void {
        $this->pathProvider->templateRootPaths = $templateRootPaths;

        self::assertSame($expected, $this->subject->getTemplateRootPaths());
    }

    /**
     * @param array<int, string> $partialRootPaths
     * @param array<int, string> $expected
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('getPartialRootPathsMergesConfigurationFromPathProvidersDataProvider')]
    public function getPartialRootPathsMergesConfigurationFromPathProviders(
        array $partialRootPaths,
        array $expected,
    ): void {
        $this->pathProvider->partialRootPaths = $partialRootPaths;

        self::assertSame($expected, $this->subject->getPartialRootPaths());
    }

    /**
     * @return \Generator<string, array{array<int, string>, array<int, string>}>
     */
    public static function getTemplateRootPathsMergesConfigurationFromPathProvidersDataProvider(): \Generator
    {
        yield 'no view configuration' => [
            [],
            [
                10 => dirname(__DIR__, 2) . '/Fixtures/Templates',
            ],
        ];
        yield 'view configuration with identical keys' => [
            [
                '10' => 'foo',
            ],
            [
                10 => 'foo',
            ],
        ];
        yield 'view configuration with additional keys' => [
            [
                '10' => 'foo',
                '20' => 'baz',
            ],
            [
                10 => 'foo',
                20 => 'baz',
            ],
        ];
    }

    /**
     * @return \Generator<string, array{array<int, string>, array<int, string>}>
     */
    public static function getPartialRootPathsMergesConfigurationFromPathProvidersDataProvider(): \Generator
    {
        yield 'no view configuration' => [
            [],
            [
                10 => dirname(__DIR__, 2) . '/Fixtures/Partials',
            ],
        ];
        yield 'view configuration with identical keys' => [
            [
                '10' => 'foo',
            ],
            [
                10 => 'foo',
            ],
        ];
        yield 'view configuration with additional keys' => [
            [
                '10' => 'foo',
                '20' => 'baz',
            ],
            [
                10 => 'foo',
                20 => 'baz',
            ],
        ];
    }
}
