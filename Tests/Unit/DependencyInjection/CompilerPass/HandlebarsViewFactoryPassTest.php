<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace CPSIT\Typo3Handlebars\Tests\Unit\DependencyInjection\CompilerPass;

use CPSIT\Typo3Handlebars as Src;
use CPSIT\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use Symfony\Component\DependencyInjection;
use TYPO3\TestingFramework;

/**
 * HandlebarsViewFactoryPassTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\DependencyInjection\CompilerPass\HandlebarsViewFactoryPass::class)]
final class HandlebarsViewFactoryPassTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Src\DependencyInjection\CompilerPass\HandlebarsViewFactoryPass $subject;
    private DependencyInjection\ContainerBuilder $container;

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\DependencyInjection\CompilerPass\HandlebarsViewFactoryPass(Tests\Unit\Fixtures\Classes\View\DummyViewFactory::class);
        $this->container = $this->createContainer();
        $this->container->addCompilerPass($this->subject);
    }

    #[Framework\Attributes\Test]
    public function processDoesNothingIfViewFactoryServiceIsMissing(): void
    {
        $expected = $this->createContainer(false);
        $expected->compile();

        self::assertFalse($expected->has(Tests\Unit\Fixtures\Classes\View\DummyViewFactory::class));

        $this->container->removeDefinition(Tests\Unit\Fixtures\Classes\View\DummyViewFactory::class);
        $this->container->compile();

        self::assertEquals($expected->getServiceIds(), $this->container->getServiceIds());
    }

    #[Framework\Attributes\Test]
    public function processDoesNothingIfNoTargetServicesWereAdded(): void
    {
        $this->container->compile();

        $expected = $this->container->get(Tests\Unit\Fixtures\Classes\DummyConsumingViewFactoryClass::class);

        self::assertInstanceOf(Tests\Unit\Fixtures\Classes\View\CoreViewFactory::class, $expected->viewFactory);
    }

    #[Framework\Attributes\Test]
    public function addMethodCallRegistersMethodCallForGivenService(): void
    {
        $this->subject->addMethodCall(
            Tests\Unit\Fixtures\Classes\DummyConsumingViewFactoryClass::class,
            'setViewFactory',
        );

        $this->container->compile();

        $expected = $this->container->get(Tests\Unit\Fixtures\Classes\DummyConsumingViewFactoryClass::class);

        self::assertInstanceOf(Tests\Unit\Fixtures\Classes\View\DummyViewFactory::class, $expected->viewFactory);
    }

    #[Framework\Attributes\Test]
    public function addMethodCallRegistersMethodCallForGivenBaseService(): void
    {
        $this->subject->addMethodCall(
            Tests\Unit\Fixtures\Classes\DummyAbstractConsumingViewFactoryClass::class,
            'setViewFactory',
        );

        $this->container->compile();

        $expected = $this->container->get(Tests\Unit\Fixtures\Classes\DummyConsumingViewFactoryClass::class);

        self::assertInstanceOf(Tests\Unit\Fixtures\Classes\View\DummyViewFactory::class, $expected->viewFactory);
    }

    #[Framework\Attributes\Test]
    public function addPropertyRegistersConstructorArgumentForGivenService(): void
    {
        $this->subject->addProperty(Tests\Unit\Fixtures\Classes\DummyConsumingViewFactoryClass::class);

        $this->container->compile();

        $expected = $this->container->get(Tests\Unit\Fixtures\Classes\DummyConsumingViewFactoryClass::class);

        self::assertInstanceOf(Tests\Unit\Fixtures\Classes\View\DummyViewFactory::class, $expected->viewFactory);
    }

    private function createContainer(bool $addViewFactory = true): DependencyInjection\ContainerBuilder
    {
        $definition = new DependencyInjection\Definition();
        $definition->setAutowired(true);
        $definition->setPublic(true);

        $container = new DependencyInjection\ContainerBuilder();
        $container->setDefinition(Tests\Unit\Fixtures\Classes\View\ViewFactory::class, clone $definition);
        $container->setDefinition(Tests\Unit\Fixtures\Classes\View\CoreViewFactory::class, clone $definition);
        $container->setDefinition(Tests\Unit\Fixtures\Classes\DummyConsumingViewFactoryClass::class, clone $definition);
        $container->setAlias(Tests\Unit\Fixtures\Classes\View\ViewFactory::class, Tests\Unit\Fixtures\Classes\View\CoreViewFactory::class);

        if ($addViewFactory) {
            $container->setDefinition(Tests\Unit\Fixtures\Classes\View\DummyViewFactory::class, clone $definition);
        }

        return $container;
    }
}
