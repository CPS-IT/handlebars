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

namespace Fr\Typo3Handlebars\Tests\Unit\DependencyInjection\Extension;

use Fr\Typo3Handlebars as Src;
use PHPUnit\Framework;
use Symfony\Component\DependencyInjection;
use TYPO3\TestingFramework;

/**
 * HandlebarsExtensionTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\DependencyInjection\Extension\HandlebarsExtension::class)]
final class HandlebarsExtensionTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Src\DependencyInjection\Extension\HandlebarsExtension $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\DependencyInjection\Extension\HandlebarsExtension();
    }

    /**
     * @param array<int, mixed>[] $configs
     * @param array<string, mixed> $expectedParameters
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('loadAddsResolvedParametersToContainerDataProvider')]
    public function loadAddsResolvedParametersToContainer(array $configs, array $expectedParameters): void
    {
        $container = new DependencyInjection\ContainerBuilder();

        $this->subject->load($configs, $container);

        foreach ($expectedParameters as $parameterName => $parameterValue) {
            self::assertTrue($container->hasParameter($parameterName));
            self::assertSame($parameterValue, $container->getParameter($parameterName));
        }
    }

    /**
     * @return \Generator<string, array<mixed>>
     */
    public static function loadAddsResolvedParametersToContainerDataProvider(): \Generator
    {
        $firstRootPaths = [
            10 => 'EXT:foo/baz',
            100 => 'EXT:baz/foo',
        ];
        $secondRootPaths = [
            50 => '/assets',
            10 => 'EXT:hello_world',
        ];
        $expectedRootPaths = [
            10 => 'EXT:hello_world',
            100 => 'EXT:baz/foo',
            50 => '/assets',
        ];

        yield 'no configs' => [
            [],
            [
                Src\DependencyInjection\Extension\HandlebarsExtension::PARAMETER_DEFAULT_DATA => [],
                Src\DependencyInjection\Extension\HandlebarsExtension::PARAMETER_TEMPLATE_ROOT_PATHS => [],
                Src\DependencyInjection\Extension\HandlebarsExtension::PARAMETER_PARTIAL_ROOT_PATHS => [],
            ],
        ];
        yield 'default data' => [
            [
                [
                    'default_data' => [
                        'foo' => 'baz',
                    ],
                ],
                [
                    'default_data' => [
                        'foo' => 'yay',
                        'baz' => 'foo',
                    ],
                ],
            ],
            [
                Src\DependencyInjection\Extension\HandlebarsExtension::PARAMETER_DEFAULT_DATA => [
                    'foo' => 'yay',
                    'baz' => 'foo',
                ],
                Src\DependencyInjection\Extension\HandlebarsExtension::PARAMETER_TEMPLATE_ROOT_PATHS => [],
                Src\DependencyInjection\Extension\HandlebarsExtension::PARAMETER_PARTIAL_ROOT_PATHS => [],
            ],
        ];
        yield 'template root paths' => [
            [
                [
                    'view' => [
                        'templateRootPaths' => $firstRootPaths,
                    ],
                ],
                [
                    'view' => [
                        'templateRootPaths' => $secondRootPaths,
                    ],
                ],
            ],
            [
                Src\DependencyInjection\Extension\HandlebarsExtension::PARAMETER_DEFAULT_DATA => [],
                Src\DependencyInjection\Extension\HandlebarsExtension::PARAMETER_TEMPLATE_ROOT_PATHS => $expectedRootPaths,
                Src\DependencyInjection\Extension\HandlebarsExtension::PARAMETER_PARTIAL_ROOT_PATHS => [],
            ],
        ];
        yield 'partial root paths' => [
            [
                [
                    'view' => [
                        'partialRootPaths' => $firstRootPaths,
                    ],
                ],
                [
                    'view' => [
                        'partialRootPaths' => $secondRootPaths,
                    ],
                ],
            ],
            [
                Src\DependencyInjection\Extension\HandlebarsExtension::PARAMETER_DEFAULT_DATA => [],
                Src\DependencyInjection\Extension\HandlebarsExtension::PARAMETER_TEMPLATE_ROOT_PATHS => [],
                Src\DependencyInjection\Extension\HandlebarsExtension::PARAMETER_PARTIAL_ROOT_PATHS => $expectedRootPaths,
            ],
        ];
    }
}
