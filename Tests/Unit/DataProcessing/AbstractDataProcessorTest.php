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

use Fr\Typo3Handlebars as Src;
use Fr\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use Psr\Log;
use Symfony\Component\EventDispatcher;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Frontend;
use TYPO3\TestingFramework;

/**
 * AbstractDataProcessorTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\DataProcessing\AbstractDataProcessor::class)]
final class AbstractDataProcessorTest extends TestingFramework\Core\Unit\UnitTestCase
{
    use Tests\HandlebarsTemplateResolverTrait;
    use Tests\Unit\HandlebarsCacheTrait;

    private Log\Test\TestLogger $logger;
    private Tests\Unit\Fixtures\Classes\DummyConfigurationManager $configurationManager;
    private Tests\Unit\Fixtures\Classes\Presenter\DummyPresenter $presenter;
    private Tests\Unit\Fixtures\Classes\Data\DummyProvider $provider;
    private Tests\Unit\Fixtures\Classes\DataProcessing\DummyProcessor $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = new Log\Test\TestLogger();
        $this->configurationManager = new Tests\Unit\Fixtures\Classes\DummyConfigurationManager();
        $this->presenter = new Tests\Unit\Fixtures\Classes\Presenter\DummyPresenter(
            new Src\Renderer\HandlebarsRenderer(
                $this->getCache(),
                new EventDispatcher\EventDispatcher(),
                new Src\Renderer\Helper\HelperRegistry($this->logger),
                $this->logger,
                $this->getTemplateResolver(),
                new Src\Renderer\Variables\VariableBag([]),
            ),
        );
        $this->provider = new Tests\Unit\Fixtures\Classes\Data\DummyProvider();
        $this->subject = new Tests\Unit\Fixtures\Classes\DataProcessing\DummyProcessor();
        $this->subject->setPresenter($this->presenter);
        $this->subject->setProvider($this->provider);
        $this->subject->setLogger($this->logger);
        $this->subject->injectConfigurationManager($this->configurationManager);
    }

    #[Framework\Attributes\Test]
    public function processLogsCriticalErrorIfRenderingFails(): void
    {
        $this->subject->shouldThrowException = true;

        self::assertSame('', $this->subject->process('', []));
        self::assertTrue($this->logger->hasCriticalThatPasses(function ($logRecord) {
            $expectedMessage = 'Data processing for ' . $this->subject::class . ' failed.';
            self::assertSame($expectedMessage, $logRecord['message']);
            self::assertInstanceOf(Src\Exception\UnableToPresentException::class, $logRecord['context']['exception']);
            return true;
        }));
    }

    #[Framework\Attributes\Test]
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

    #[Framework\Attributes\Test]
    public function processInitializesConfigurationManager(): void
    {
        $contentObjectRenderer = new Frontend\ContentObject\ContentObjectRenderer();

        $this->configurationManager->setConfiguration(['foo' => 'baz']);

        self::assertSame(
            ['foo' => 'baz'],
            $this->configurationManager->getConfiguration(
                Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT,
            ),
        );
        self::assertNull($this->configurationManager->getContentObject());

        $this->subject->shouldInitializeConfigurationManager = true;
        $this->subject->setContentObjectRenderer($contentObjectRenderer);
        $this->subject->process('', []);

        self::assertSame(
            ['foo' => 'baz'],
            $this->configurationManager->getConfiguration(
                Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT,
            ),
        );
        self::assertSame($contentObjectRenderer, $this->configurationManager->getContentObject());
    }

    #[Framework\Attributes\Test]
    public function setContentObjectRendererAppliesContentObjectRenderer(): void
    {
        $contentObjectRenderer = new Frontend\ContentObject\ContentObjectRenderer();

        $this->subject->setContentObjectRenderer($contentObjectRenderer);

        self::assertSame($contentObjectRenderer, $this->subject->getContentObjectRenderer());
    }
}
