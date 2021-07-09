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

namespace Fr\Typo3Handlebars\Tests\Unit\DataProcessing;

use Fr\Typo3Handlebars\Exception\UnableToPresentException;
use Fr\Typo3Handlebars\Renderer\HandlebarsRenderer;
use Fr\Typo3Handlebars\Tests\Unit\Fixtures\Classes\Data\DummyProvider;
use Fr\Typo3Handlebars\Tests\Unit\Fixtures\Classes\DataProcessing\DummyProcessor;
use Fr\Typo3Handlebars\Tests\Unit\Fixtures\Classes\Presenter\DummyPresenter;
use Fr\Typo3Handlebars\Tests\Unit\HandlebarsCacheTrait;
use Fr\Typo3Handlebars\Tests\Unit\HandlebarsTemplateResolverTrait;
use Psr\Log\Test\TestLogger;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * AbstractDataProcessorTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class AbstractDataProcessorTest extends UnitTestCase
{
    use HandlebarsCacheTrait;
    use HandlebarsTemplateResolverTrait;

    /**
     * @var TestLogger
     */
    protected $logger;

    /**
     * @var DummyPresenter
     */
    protected $presenter;

    /**
     * @var DummyProvider
     */
    protected $provider;

    /**
     * @var DummyProcessor
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = new TestLogger();
        $this->presenter = new DummyPresenter(new HandlebarsRenderer($this->getCache(), $this->getTemplateResolver()));
        $this->provider = new DummyProvider();
        $this->subject = new DummyProcessor();
        $this->subject->setPresenter($this->presenter);
        $this->subject->setProvider($this->provider);
        $this->subject->setLogger($this->logger);
    }

    /**
     * @test
     */
    public function processLogsCriticalErrorIfRenderingFails(): void
    {
        $this->subject->shouldThrowException = true;

        self::assertSame('', $this->subject->process('', []));
        self::assertTrue($this->logger->hasCriticalThatPasses(function ($logRecord) {
            $expectedMessage = 'Data processing for ' . get_class($this->subject) . ' failed.';
            static::assertSame($expectedMessage, $logRecord['message']);
            static::assertInstanceOf(UnableToPresentException::class, $logRecord['context']['exception']);
            return true;
        }));
    }

    /**
     * @test
     */
    public function processReturnsRenderedContent(): void
    {
        $this->provider->expectedData = ['foo' => 'baz'];

        // Test whether content is respected
        $expected = 'foo: {"foo":"baz"}';
        self::assertSame($expected, $this->subject->process('foo: ', []));

        // Test whether configuration is respected
        $expected = '{"foo":"baz"} {"another":"foo"}';
        self::assertSame($expected, $this->subject->process('', ['another' => 'foo']));
    }
}
