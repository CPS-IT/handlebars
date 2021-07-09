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

use Fr\Typo3Handlebars\DependencyInjection\ProcessingBridge;
use Fr\Typo3Handlebars\Tests\Unit\Fixtures\Classes\Data\DummyProvider;
use Fr\Typo3Handlebars\Tests\Unit\Fixtures\Classes\DataProcessing\DummyProcessor;
use Fr\Typo3Handlebars\Tests\Unit\Fixtures\Classes\Presenter\DummyPresenter;
use Symfony\Component\DependencyInjection\Definition;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * ProcessingBridgeTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class ProcessingBridgeTest extends UnitTestCase
{
    /**
     * @var ProcessingBridge
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new ProcessingBridge(DummyProcessor::class, new Definition());
    }

    /**
     * @test
     */
    public function constructorThrowsExceptionIfGivenServiceIdIsInvalid(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionCode(1606326944);

        new ProcessingBridge(static::class, new Definition());
    }

    /**
     * @test
     */
    public function getPresenterReturnsCorrectlyEvaluatedPresenterReference(): void
    {
        self::assertSame(DummyPresenter::class, (string)$this->subject->getPresenter());
    }

    /**
     * @test
     */
    public function getProviderReturnsCorrectlyEvaluatedProviderReference(): void
    {
        self::assertSame(DummyProvider::class, (string)$this->subject->getProvider());
    }

    /**
     * @test
     */
    public function hasMethodCallDefinesWhetherCallForGivenMethodIsRegistered(): void
    {
        self::assertFalse($this->subject->hasMethodCall('foo'));

        $definition = new Definition();
        $definition->addMethodCall('foo');
        $subject = new ProcessingBridge(DummyProcessor::class, $definition);
        self::assertTrue($subject->hasMethodCall('foo'));
    }
}
