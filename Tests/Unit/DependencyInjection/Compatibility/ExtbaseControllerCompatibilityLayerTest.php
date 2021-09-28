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

use Fr\Typo3Handlebars\Compatibility\View\HandlebarsViewResolver;
use Fr\Typo3Handlebars\DependencyInjection\Compatibility\ExtbaseControllerCompatibilityLayer;
use Fr\Typo3Handlebars\Tests\Unit\Fixtures\Classes\DataProcessing\DummyProcessor;
use Fr\Typo3Handlebars\Tests\Unit\Fixtures\Classes\DataProcessing\LogProcessor;
use Fr\Typo3Handlebars\Tests\Unit\Fixtures\Classes\DummyController;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * ExtbaseControllerCompatibilityLayerTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class ExtbaseControllerCompatibilityLayerTest extends UnitTestCase
{
    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * @var ExtbaseControllerCompatibilityLayer
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new ContainerBuilder();
        $this->container->setDefinition(HandlebarsViewResolver::class, new Definition(HandlebarsViewResolver::class));
        $this->container->setDefinition(DummyController::class, new Definition(DummyController::class));

        $this->subject = new ExtbaseControllerCompatibilityLayer($this->container);
    }

    /**
     * @test
     */
    public function provideThrowsExceptionOnMissingController(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1632814271);

        $this->subject->provide('foo', []);
    }

    /**
     * @test
     */
    public function provideThrowsExceptionOnMissingControllerDefinition(): void
    {
        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionCode(1632814362);

        $this->subject->provide('foo', ['controller' => 'foo']);
    }

    /**
     * @test
     */
    public function provideThrowsExceptionOnInvalidControllerDefinition(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1632814520);

        $this->container->setDefinition('foo', new Definition());
        $this->subject->provide('foo', ['controller' => 'foo']);
    }

    /**
     * @test
     */
    public function provideThrowsExceptionOnUnsupportedController(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1632814592);

        $this->container->setDefinition('foo', new Definition(\Exception::class));
        $this->subject->provide('foo', ['controller' => 'foo']);
    }

    /**
     * @test
     */
    public function provideThrowsExceptionOnInvalidActions(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1632814413);

        $this->container->setDefinition('foo', new Definition(DummyController::class));
        $this->subject->provide('foo', ['controller' => 'foo', 'actions' => false]);
    }

    /**
     * @test
     */
    public function provideAddsProcessorMapToViewResolverAndInjectsViewResolverIntoController(): void
    {
        $processorServiceId = DummyProcessor::class;
        $processorReference = new Reference($processorServiceId);
        $configuration = [
            'controller' => DummyController::class,
            'actions' => 'foo,baz',
        ];

        $this->subject->provide($processorServiceId, $configuration);

        $expectedViewResolverMethodCalls = [
            [
                'setProcessorMap',
                [
                    [
                        DummyController::class => [
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
                    new Reference(HandlebarsViewResolver::class),
                ],
            ],
        ];

        self::assertEquals(
            $expectedViewResolverMethodCalls,
            $this->container->getDefinition(HandlebarsViewResolver::class)->getMethodCalls()
        );
        self::assertEquals(
            $expectedControllerMethodCalls,
            $this->container->getDefinition(DummyController::class)->getMethodCalls()
        );
    }

    /**
     * @test
     */
    public function provideMergesPreconfiguredProcessorMapWithNewProcessorMap(): void
    {
        $firstProcessorServiceId = DummyProcessor::class;
        $firstProcessorReference = new Reference($firstProcessorServiceId);
        $firstConfiguration = [
            'controller' => DummyController::class,
            'actions' => 'foo,baz',
        ];

        $secondProcessorServiceId = LogProcessor::class;
        $secondProcessorReference = new Reference($secondProcessorServiceId);
        $secondConfiguration = [
            'controller' => DummyController::class,
        ];

        $this->subject->provide($firstProcessorServiceId, $firstConfiguration);
        $this->subject->provide($secondProcessorServiceId, $secondConfiguration);

        $expectedViewResolverMethodCalls = [
            // Index is 1 since the first method call is removed in the second provide() call
            1 => [
                'setProcessorMap',
                [
                    [
                        DummyController::class => [
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
                    new Reference(HandlebarsViewResolver::class),
                ],
            ],
        ];

        self::assertEquals(
            $expectedViewResolverMethodCalls,
            $this->container->getDefinition(HandlebarsViewResolver::class)->getMethodCalls()
        );
        self::assertEquals(
            $expectedControllerMethodCalls,
            $this->container->getDefinition(DummyController::class)->getMethodCalls()
        );
    }
}
