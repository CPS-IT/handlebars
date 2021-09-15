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

use Fr\Typo3Handlebars\Renderer\Template\TemplatePaths;
use Fr\Typo3Handlebars\Tests\Unit\Fixtures\Classes\DummyConfigurationManager;
use Fr\Typo3Handlebars\Tests\Unit\HandlebarsTemplateResolverTrait;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * TemplatePathsTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class TemplatePathsTest extends UnitTestCase
{
    use HandlebarsTemplateResolverTrait;

    /**
     * @var DummyConfigurationManager
     */
    protected $configurationManager;

    /**
     * @var TemplatePaths
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configurationManager = new DummyConfigurationManager();
        $this->subject = new TemplatePaths($this->configurationManager);
        $this->subject->setContainer($this->getContainer());
    }

    /**
     * @test
     * @dataProvider getMergesConfigurationFromContainerWithTypoScriptConfigurationDataProvider
     * @param array<string, array> $typoScriptConfiguration
     * @param string[] $expected
     */
    public function getMergesConfigurationFromContainerWithTypoScriptConfiguration(
        array $typoScriptConfiguration,
        array $expected
    ): void {
        $this->configurationManager->setConfiguration($typoScriptConfiguration);

        self::assertSame($expected, $this->subject->get());
    }

    /**
     * @return \Generator<string, array>
     */
    public function getMergesConfigurationFromContainerWithTypoScriptConfigurationDataProvider(): \Generator
    {
        yield 'no TypoScript configuration' => [
            [],
            [
                10 => $this->getTemplateRootPath(),
            ],
        ];
        yield 'TypoScript configuration with identical keys' => [
            [
                'view' => [
                    'templateRootPaths' => [
                        '10' => 'foo',
                    ],
                ],
            ],
            [
                10 => 'foo',
            ],
        ];
        yield 'TypoScript configuration with additional keys' => [
            [
                'view' => [
                    'templateRootPaths' => [
                        '10' => 'foo',
                        '20' => 'baz',
                    ],
                ],
            ],
            [
                10 => 'foo',
                20 => 'baz',
            ],
        ];
    }
}
