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
use TYPO3\CMS\Core;
use TYPO3\CMS\Frontend;
use TYPO3\TestingFramework;

/**
 * SimpleProcessorTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\DataProcessing\SimpleProcessor::class)]
final class SimpleProcessorTest extends TestingFramework\Core\Unit\UnitTestCase
{
    use Tests\HandlebarsTemplateResolverTrait;
    use Tests\Unit\HandlebarsCacheTrait;

    private Frontend\ContentObject\ContentObjectRenderer&Framework\MockObject\MockObject $contentObjectRendererMock;
    private Log\Test\TestLogger $logger;
    private Src\Renderer\HandlebarsRenderer $renderer;
    private Src\DataProcessing\SimpleProcessor $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contentObjectRendererMock = $this->createMock(Frontend\ContentObject\ContentObjectRenderer::class);
        $this->logger = new Log\Test\TestLogger();
        $this->renderer = new Src\Renderer\HandlebarsRenderer(
            $this->getCache(),
            new EventDispatcher\EventDispatcher(),
            $this->logger,
            $this->getTemplateResolver(),
        );
        $this->subject = new Src\DataProcessing\SimpleProcessor($this->logger, $this->renderer);
        $this->subject->setContentObjectRenderer($this->contentObjectRendererMock);

        $GLOBALS['TYPO3_REQUEST'] = new Core\Http\ServerRequest();
        $GLOBALS['TSFE'] = $this->createMock(Frontend\Controller\TypoScriptFrontendController::class);
    }

    /**
     * @param array<string, array<string, string|null>> $configuration
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('processThrowsExceptionIfTemplatePathIsNotConfiguredDataProvider')]
    public function processThrowsExceptionIfTemplatePathIsNotConfigured(array $configuration): void
    {
        self::assertSame('', $this->subject->process('', $configuration));
        self::assertTrue($this->logger->hasCriticalThatPasses(function ($logRecord) {
            $expectedMessage = 'Data processing for ' . $this->subject::class . ' failed.';
            self::assertSame($expectedMessage, $logRecord['message']);
            self::assertInstanceOf(Src\Exception\InvalidTemplateFileException::class, $logRecord['context']['exception']);
            self::assertSame(1606834398, $logRecord['context']['exception']->getCode());
            return true;
        }));
    }

    #[Framework\Attributes\Test]
    public function processReturnsRenderedTemplate(): void
    {
        $this->renderer->registerHelper('varDump', Src\Renderer\Helper\VarDumpHelper::class);

        $this->contentObjectRendererMock->data = [
            'uid' => 1,
            'pid' => 0,
            'title' => 'foo',
            'comment' => 'baz',
        ];

        $configuration = [
            'userFunc.' => [
                'templatePath' => 'DummyTemplateVariables',
            ],
        ];

        Core\Utility\DebugUtility::useAnsiColor(false);

        $expected = <<<EOF
Debug
array(4 items)
   uid => 1 (integer)
   pid => 0 (integer)
   title => "foo" (3 chars)
   comment => "baz" (3 chars)
EOF;

        self::assertSame(trim($expected), trim($this->subject->process('', $configuration)));

        Core\Utility\DebugUtility::useAnsiColor(true);
    }

    /**
     * @return \Generator<string, array<mixed>>
     */
    public static function processThrowsExceptionIfTemplatePathIsNotConfiguredDataProvider(): \Generator
    {
        yield 'empty array' => [[]];
        yield 'missing template path' => [['userFunc.' => []]];
        yield 'invalid template path' => [['userFunc.' => ['templatePath' => null]]];
        yield 'empty template path' => [['userFunc.' => ['templatePath' => '   ']]];
    }

    protected function tearDown(): void
    {
        self::assertTrue($this->clearCache(), 'Unable to clear Handlebars cache.');

        unset($GLOBALS['TYPO3_REQUEST']);
        unset($GLOBALS['TSFE']);

        parent::tearDown();
    }
}
