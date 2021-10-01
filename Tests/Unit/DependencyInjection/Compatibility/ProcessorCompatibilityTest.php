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
use Fr\Typo3Handlebars\DependencyInjection\Compatibility\ProcessorCompatibility;
use Fr\Typo3Handlebars\Exception\UnsupportedTypeException;
use Fr\Typo3Handlebars\Tests\Unit\Fixtures\Classes\DummyController;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * ProcessorCompatibilityTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class ProcessorCompatibilityTest extends UnitTestCase
{
    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * @var ProcessorCompatibility
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new ContainerBuilder();
        $this->container->setDefinition(HandlebarsViewResolver::class, new Definition(HandlebarsViewResolver::class));
        $this->container->setDefinition(DummyController::class, new Definition(DummyController::class));

        $this->subject = new ProcessorCompatibility(
            'foo',
            [
                'type' => ExtbaseControllerCompatibilityLayer::TYPE,
                'controller' => DummyController::class,
                'actions' => 'dummy',
            ],
            $this->container
        );
    }

    /**
     * @test
     */
    public function constructorThrowsExceptionOnMissingType(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionCode(1632813645);

        new ProcessorCompatibility('foo', [], $this->container);
    }

    /**
     * @test
     */
    public function provideCompatibilityThrowsExceptionOnUnsupportedType(): void
    {
        $this->expectException(UnsupportedTypeException::class);
        $this->expectExceptionCode(1632813839);

        $subject = new ProcessorCompatibility('foo', ['type' => 'foo'], $this->container);
        $subject->provideCompatibility();
    }

    /**
     * @test
     */
    public function provideCompatibilityAddsCompatibilityLayerBasedOnType(): void
    {
        $controllerDefinition = $this->container->getDefinition(DummyController::class);

        self::assertFalse($controllerDefinition->hasMethodCall('injectViewResolver'));

        $this->subject->provideCompatibility();

        self::assertTrue($controllerDefinition->hasMethodCall('injectViewResolver'));
    }
}
