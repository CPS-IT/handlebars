<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2020 Elias Häußler <e.haeussler@familie-redlich.de>
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

namespace Fr\Typo3Handlebars\Tests\Unit\DependencyInjection;

use Fr\Typo3Handlebars as Src;
use Fr\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use Symfony\Component\DependencyInjection;
use TYPO3\TestingFramework;

/**
 * ProcessingBridgeTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\DependencyInjection\ProcessingBridge::class)]
final class ProcessingBridgeTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Src\DependencyInjection\ProcessingBridge $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\DependencyInjection\ProcessingBridge(
            Tests\Unit\Fixtures\Classes\DataProcessing\DummyProcessor::class,
            new DependencyInjection\Definition(),
        );
    }

    #[Framework\Attributes\Test]
    public function constructorThrowsExceptionIfGivenServiceIdIsInvalid(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionCode(1606326944);

        new Src\DependencyInjection\ProcessingBridge(self::class, new DependencyInjection\Definition());
    }

    #[Framework\Attributes\Test]
    public function getPresenterReturnsCorrectlyEvaluatedPresenterReference(): void
    {
        self::assertSame(
            Tests\Unit\Fixtures\Classes\Presenter\DummyPresenter::class,
            (string)$this->subject->getPresenter(),
        );
    }

    #[Framework\Attributes\Test]
    public function getProviderReturnsCorrectlyEvaluatedProviderReference(): void
    {
        self::assertSame(
            Tests\Unit\Fixtures\Classes\Data\DummyProvider::class,
            (string)$this->subject->getProvider(),
        );
    }

    #[Framework\Attributes\Test]
    public function hasMethodCallDefinesWhetherCallForGivenMethodIsRegistered(): void
    {
        self::assertFalse($this->subject->hasMethodCall('foo'));

        $definition = new DependencyInjection\Definition();
        $definition->addMethodCall('foo');
        $subject = new Src\DependencyInjection\ProcessingBridge(
            Tests\Unit\Fixtures\Classes\DataProcessing\DummyProcessor::class,
            $definition,
        );

        self::assertTrue($subject->hasMethodCall('foo'));
    }
}
