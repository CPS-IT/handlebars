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

use Fr\Typo3Handlebars\DataProcessing\SimpleProcessor;
use Fr\Typo3Handlebars\Exception\InvalidTemplateFileException;
use Fr\Typo3Handlebars\Renderer\HandlebarsRenderer;
use Fr\Typo3Handlebars\Renderer\Helper\VarDumpHelper;
use Fr\Typo3Handlebars\Tests\Unit\HandlebarsCacheTrait;
use Fr\Typo3Handlebars\Tests\Unit\HandlebarsTemplateResolverTrait;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\Test\TestLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * SimpleProcessorTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class SimpleProcessorTest extends UnitTestCase
{
    use HandlebarsCacheTrait;
    use HandlebarsTemplateResolverTrait;
    use ProphecyTrait;

    /**
     * @var ObjectProphecy|ContentObjectRenderer
     */
    protected $contentObjectRendererProphecy;

    /**
     * @var TestLogger
     */
    protected $logger;

    /**
     * @var HandlebarsRenderer
     */
    protected $renderer;

    /**
     * @var SimpleProcessor
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contentObjectRendererProphecy = $this->prophesize(ContentObjectRenderer::class);
        $this->logger = new TestLogger();
        $this->renderer = new HandlebarsRenderer($this->getCache(), new EventDispatcher(), $this->getTemplateResolver());
        $this->subject = new SimpleProcessor($this->renderer);
        $this->subject->cObj = $this->contentObjectRendererProphecy->reveal();
        $this->subject->setLogger($this->logger);

        $GLOBALS['TYPO3_REQUEST'] = new ServerRequest();
        $GLOBALS['TSFE'] = $this->prophesize(TypoScriptFrontendController::class)->reveal();
    }

    /**
     * @test
     * @dataProvider processThrowsExceptionIfTemplatePathIsNotConfiguredDataProvider
     * @param array<string, array<string, string|null>> $configuration
     */
    public function processThrowsExceptionIfTemplatePathIsNotConfigured(array $configuration): void
    {
        self::assertSame('', $this->subject->process('', $configuration));
        self::assertTrue($this->logger->hasCriticalThatPasses(function ($logRecord) {
            $expectedMessage = 'Data processing for ' . get_class($this->subject) . ' failed.';
            static::assertSame($expectedMessage, $logRecord['message']);
            static::assertInstanceOf(InvalidTemplateFileException::class, $logRecord['context']['exception']);
            static::assertSame(1606834398, $logRecord['context']['exception']->getCode());
            return true;
        }));
    }

    /**
     * @test
     */
    public function processReturnsRenderedTemplate(): void
    {
        $this->renderer->registerHelper('varDump', VarDumpHelper::class . '::evaluate');

        $data = [
            'uid' => 1,
            'pid' => 0,
            'title' => 'foo',
            'comment' => 'baz',
        ];
        $this->contentObjectRendererProphecy->data = $data;

        $configuration = [
            'userFunc.' => [
                'templatePath' => 'DummyTemplateVariables',
            ],
        ];

        $expected = print_r($data, true);
        self::assertSame(trim($expected), trim($this->subject->process('', $configuration)));
    }

    /**
     * @return \Generator<string, array<mixed>>
     */
    public function processThrowsExceptionIfTemplatePathIsNotConfiguredDataProvider(): \Generator
    {
        yield 'empty array' => [[]];
        yield 'missing template path' => [['userFunc.' => []]];
        yield 'invalid template path' => [['userFunc.' => ['templatePath' => null]]];
        yield 'empty template path' => [['userFunc.' => ['templatePath' => '   ']]];
    }

    protected function tearDown(): void
    {
        self::assertTrue($this->clearCache(), 'Unable to clear Handlebars cache.');
        parent::tearDown();
    }
}
