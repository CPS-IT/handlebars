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

namespace Fr\Typo3Handlebars\Tests\Unit\DependencyInjection\Compatibility;

use Fr\Typo3Handlebars as Src;
use Fr\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use Symfony\Component\DependencyInjection;
use TYPO3\TestingFramework;

/**
 * ProcessorCompatibilityTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\DependencyInjection\Compatibility\ProcessorCompatibility::class)]
final class ProcessorCompatibilityTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private DependencyInjection\ContainerBuilder $container;
    private Src\DependencyInjection\Compatibility\ProcessorCompatibility $subject;

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

        $this->subject = new Src\DependencyInjection\Compatibility\ProcessorCompatibility(
            'foo',
            [
                'type' => Src\DependencyInjection\Compatibility\ExtbaseControllerCompatibilityLayer::TYPE,
                'controller' => Tests\Unit\Fixtures\Classes\DummyController::class,
                'actions' => 'dummy',
            ],
            $this->container,
        );
    }

    #[Framework\Attributes\Test]
    public function constructorThrowsExceptionOnMissingType(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionCode(1632813645);

        new Src\DependencyInjection\Compatibility\ProcessorCompatibility('foo', [], $this->container);
    }

    #[Framework\Attributes\Test]
    public function provideCompatibilityThrowsExceptionOnUnsupportedType(): void
    {
        $this->expectException(Src\Exception\UnsupportedTypeException::class);
        $this->expectExceptionCode(1632813839);

        $subject = new Src\DependencyInjection\Compatibility\ProcessorCompatibility(
            'foo',
            ['type' => 'foo'],
            $this->container,
        );
        $subject->provideCompatibility();
    }

    #[Framework\Attributes\Test]
    public function provideCompatibilityAddsCompatibilityLayerBasedOnType(): void
    {
        $controllerDefinition = $this->container->getDefinition(Tests\Unit\Fixtures\Classes\DummyController::class);

        self::assertFalse($controllerDefinition->hasMethodCall('injectViewResolver'));

        $this->subject->provideCompatibility();

        self::assertTrue($controllerDefinition->hasMethodCall('injectViewResolver'));
    }
}
