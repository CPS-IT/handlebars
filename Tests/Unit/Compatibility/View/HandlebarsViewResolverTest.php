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

namespace Fr\Typo3Handlebars\Tests\Unit\Compatibility\View;

use Fr\Typo3Handlebars\Compatibility\View\ExtbaseViewAdapter;
use Fr\Typo3Handlebars\Compatibility\View\HandlebarsViewResolver;
use Fr\Typo3Handlebars\Tests\Unit\Fixtures\Classes\DataProcessing\DummyProcessor;
use Fr\Typo3Handlebars\Tests\Unit\Fixtures\Classes\DummyView;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\DependencyInjection\Container;
use TYPO3\CMS\Extbase\Mvc\View\GenericViewResolver;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * HandlebarsViewResolverTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class HandlebarsViewResolverTest extends UnitTestCase
{
    use ProphecyTrait;

    /**
     * @var HandlebarsViewResolver
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        // Handle different constructor arguments between TYPO3 11.4+ and lower
        $reflectionClass = new \ReflectionClass(GenericViewResolver::class);
        $firstConstructorArgumentReflection = $reflectionClass->getConstructor()->getParameters()[0];
        if ($firstConstructorArgumentReflection->getClass()->getName() === ObjectManager::class) {
            $objectManagerProphecy = $this->prophesize(ObjectManager::class);
            $objectManagerProphecy->get('foo')->willReturn(new DummyView());
            $firstConstructorArgument = $objectManagerProphecy->reveal();
        } else {
            $container = new Container();
            $container->set('foo', new DummyView());
            $firstConstructorArgument = $container;
        }

        $this->subject = new HandlebarsViewResolver($firstConstructorArgument);
        $this->subject->setDefaultViewClass('foo');
        $this->subject->setProcessorMap([
            'FooController' => [
                '_all' => new DummyProcessor(),
                'foo' => new DummyProcessor(),
            ],
        ]);
    }

    /**
     * @test
     */
    public function resolveReturnsViewFromDefaultResolverIfControllerIsNotSupported(): void
    {
        self::assertNotInstanceOf(
            ExtbaseViewAdapter::class,
            $this->subject->resolve('BazController', 'foo', 'html')
        );
    }

    /**
     * @test
     */
    public function resolveReturnsExtbaseViewAdapterForSupportedControllerAndSpecificAction(): void
    {
        self::assertInstanceOf(
            ExtbaseViewAdapter::class,
            $this->subject->resolve('FooController', 'foo', 'html')
        );
    }

    /**
     * @test
     */
    public function resolveReturnsExtbaseViewAdapterForSupportedControllerAndGeneralAction(): void
    {
        self::assertInstanceOf(
            ExtbaseViewAdapter::class,
            $this->subject->resolve('FooController', 'baz', 'html')
        );
    }
}
