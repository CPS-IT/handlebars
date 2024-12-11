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

namespace Fr\Typo3Handlebars\Tests\Unit\DependencyInjection\Compatibility;

use Fr\Typo3Handlebars as Src;
use Fr\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use Symfony\Component\DependencyInjection;
use TYPO3\TestingFramework;

/**
 * ExtbaseControllerCompatibilityLayerTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\DependencyInjection\Compatibility\ExtbaseControllerCompatibilityLayer::class)]
final class ExtbaseControllerCompatibilityLayerTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private DependencyInjection\ContainerBuilder $container;
    private Src\DependencyInjection\Compatibility\ExtbaseControllerCompatibilityLayer $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new DependencyInjection\ContainerBuilder();
        $this->container->setDefinition(
            Src\Compatibility\View\HandlebarsViewResolver::class,
            new DependencyInjection\Definition(Src\Compatibility\View\HandlebarsViewResolver::class),
        );
        $this->container->setDefinition(
            Tests\Unit\Fixtures\Classes\DummyController::class,
            new DependencyInjection\Definition(Tests\Unit\Fixtures\Classes\DummyController::class),
        );

        $this->subject = new Src\DependencyInjection\Compatibility\ExtbaseControllerCompatibilityLayer($this->container);
    }

    #[Framework\Attributes\Test]
    public function provideThrowsExceptionOnMissingController(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1632814271);

        $this->subject->provide('foo', []);
    }

    #[Framework\Attributes\Test]
    public function provideThrowsExceptionOnMissingControllerDefinition(): void
    {
        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionCode(1632814362);

        $this->subject->provide('foo', ['controller' => 'foo']);
    }

    #[Framework\Attributes\Test]
    public function provideThrowsExceptionOnInvalidControllerDefinition(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1632814520);

        $this->container->setDefinition('foo', new DependencyInjection\Definition());
        $this->subject->provide('foo', ['controller' => 'foo']);
    }

    #[Framework\Attributes\Test]
    public function provideThrowsExceptionOnUnsupportedController(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1632814592);

        $this->container->setDefinition('foo', new DependencyInjection\Definition(\Exception::class));
        $this->subject->provide('foo', ['controller' => 'foo']);
    }

    #[Framework\Attributes\Test]
    public function provideThrowsExceptionOnInvalidActions(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1632814413);

        $this->container->setDefinition('foo', new DependencyInjection\Definition(Tests\Unit\Fixtures\Classes\DummyController::class));
        $this->subject->provide('foo', ['controller' => 'foo', 'actions' => false]);
    }

    #[Framework\Attributes\Test]
    public function provideAddsProcessorMapToViewResolverAndInjectsViewResolverIntoController(): void
    {
        $processorServiceId = Tests\Unit\Fixtures\Classes\DataProcessing\DummyProcessor::class;
        $processorReference = new DependencyInjection\Reference($processorServiceId);
        $configuration = [
            'controller' => Tests\Unit\Fixtures\Classes\DummyController::class,
            'actions' => 'foo,baz',
        ];

        $this->subject->provide($processorServiceId, $configuration);

        $expectedViewResolverMethodCalls = [
            [
                'setProcessorMap',
                [
                    [
                        Tests\Unit\Fixtures\Classes\DummyController::class => [
                            'foo' => $processorReference,
                            'baz' => $processorReference,
                        ],
                    ],
                ],
            ],
        ];
        $expectedControllerMethodCalls = [
            [
                'injectViewResolver',
                [
                    new DependencyInjection\Reference(Src\Compatibility\View\HandlebarsViewResolver::class),
                ],
            ],
        ];

        self::assertEquals(
            $expectedViewResolverMethodCalls,
            $this->container->getDefinition(Src\Compatibility\View\HandlebarsViewResolver::class)->getMethodCalls()
        );
        self::assertEquals(
            $expectedControllerMethodCalls,
            $this->container->getDefinition(Tests\Unit\Fixtures\Classes\DummyController::class)->getMethodCalls()
        );
    }

    #[Framework\Attributes\Test]
    public function provideMergesPreconfiguredProcessorMapWithNewProcessorMap(): void
    {
        $firstProcessorServiceId = Tests\Unit\Fixtures\Classes\DataProcessing\DummyProcessor::class;
        $firstProcessorReference = new DependencyInjection\Reference($firstProcessorServiceId);
        $firstConfiguration = [
            'controller' => Tests\Unit\Fixtures\Classes\DummyController::class,
            'actions' => 'foo,baz',
        ];

        $secondProcessorServiceId = Tests\Unit\Fixtures\Classes\DataProcessing\LogProcessor::class;
        $secondProcessorReference = new DependencyInjection\Reference($secondProcessorServiceId);
        $secondConfiguration = [
            'controller' => Tests\Unit\Fixtures\Classes\DummyController::class,
        ];

        $this->subject->provide($firstProcessorServiceId, $firstConfiguration);
        $this->subject->provide($secondProcessorServiceId, $secondConfiguration);

        $expectedViewResolverMethodCalls = [
            // Index is 1 since the first method call is removed in the second provide() call
            1 => [
                'setProcessorMap',
                [
                    [
                        Tests\Unit\Fixtures\Classes\DummyController::class => [
                            'foo' => $firstProcessorReference,
                            'baz' => $firstProcessorReference,
                            '_all' => $secondProcessorReference,
                        ],
                    ],
                ],
            ],
        ];
        $expectedControllerMethodCalls = [
            // Index is 1 since the first method call is removed in the second provide() call
            1 => [
                'injectViewResolver',
                [
                    new DependencyInjection\Reference(Src\Compatibility\View\HandlebarsViewResolver::class),
                ],
            ],
        ];

        self::assertEquals(
            $expectedViewResolverMethodCalls,
            $this->container->getDefinition(Src\Compatibility\View\HandlebarsViewResolver::class)->getMethodCalls()
        );
        self::assertEquals(
            $expectedControllerMethodCalls,
            $this->container->getDefinition(Tests\Unit\Fixtures\Classes\DummyController::class)->getMethodCalls()
        );
    }
}
