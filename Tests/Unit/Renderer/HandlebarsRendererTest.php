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

namespace Fr\Typo3Handlebars\Tests\Unit\Renderer;

use Fr\Typo3Handlebars as Src;
use Fr\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use Psr\Log;
use Symfony\Component\EventDispatcher;
use TYPO3\CMS\Core;
use TYPO3\CMS\Frontend;
use TYPO3\TestingFramework;

/**
 * HandlebarsRendererTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\HandlebarsRenderer::class)]
final class HandlebarsRendererTest extends TestingFramework\Core\Unit\UnitTestCase
{
    use Tests\Unit\HandlebarsCacheTrait;
    use Tests\HandlebarsTemplateResolverTrait;

    private Log\Test\TestLogger $logger;
    private Src\Renderer\HandlebarsRenderer $subject;
    private Frontend\Controller\TypoScriptFrontendController&Framework\MockObject\MockObject $tsfeMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->renewSubject();
        $this->tsfeMock = $this->createMock(Frontend\Controller\TypoScriptFrontendController::class);

        $GLOBALS['TSFE'] = $this->tsfeMock;
    }

    #[Framework\Attributes\Test]
    public function renderLogsCriticalErrorIfGivenTemplateIsNotAvailable(): void
    {
        self::assertSame('', $this->subject->render('foo.baz'));
        self::assertTrue($this->logger->hasCriticalThatPasses(function ($logRecord) {
            /** @var Src\Exception\TemplateNotFoundException $exception */
            $exception = $logRecord['context']['exception'];
            self::assertInstanceOf(Src\Exception\TemplateNotFoundException::class, $exception);
            self::assertSame(1606217089, $exception->getCode());
            self::assertStringEndsWith('foo.baz', $exception->getTemplateFile());
            return true;
        }));
    }

    /**
     * @test
     * @
     */
    public function renderLogsCriticalErrorIfGivenTemplateIsNotReadable(): void
    {
        $this->templateResolver = new Tests\Unit\Fixtures\Classes\Renderer\Template\DummyTemplateResolver();
        $this->suppressNextError(E_WARNING);
        self::assertSame('', $this->renewSubject()->render('foo.baz'));
        self::assertTrue($this->logger->hasCriticalThatPasses(function ($logRecord) {
            /** @var Src\Exception\InvalidTemplateFileException $exception */
            $exception = $logRecord['context']['exception'];
            self::assertInstanceOf(Src\Exception\InvalidTemplateFileException::class, $exception);
            self::assertSame(1606217313, $exception->getCode());
            self::assertStringEndsWith('foo.baz', $exception->getTemplateFile());
            return true;
        }));
    }

    #[Framework\Attributes\Test]
    public function renderUsesGivenTemplatePathIfItIsNotAvailableWithinTemplateRootPaths(): void
    {
        self::assertSame('', $this->subject->render($this->templateRootPath . '/DummyTemplateEmpty'));
    }

    #[Framework\Attributes\Test]
    public function renderReturnsEmptyStringIfGivenTemplateIsEmpty(): void
    {
        self::assertSame('', $this->subject->render('DummyTemplateEmpty'));
    }

    #[Framework\Attributes\Test]
    public function renderMergesDefaultDataWithGivenData(): void
    {
        $this->subject->registerHelper('varDump', Src\Renderer\Helper\VarDumpHelper::class . '::evaluate');
        $this->subject->setDefaultData([
            'foo' => 'baz',
        ]);

        Core\Utility\DebugUtility::useAnsiColor(false);

        $expected = <<<EOF
Debug
array(2 items)
   foo => "baz" (3 chars)
   another => "foo" (3 chars)
EOF;

        self::assertSame(
            \trim($expected),
            \trim($this->subject->render('DummyTemplateVariables', ['another' => 'foo'])),
        );

        Core\Utility\DebugUtility::useAnsiColor(true);
    }

    #[Framework\Attributes\Test]
    public function renderUsesCachedCompileResult(): void
    {
        $this->assertCacheIsEmptyForTemplate('DummyTemplate.hbs');
        $this->getCache()->set(
            file_get_contents(
                $this->getTemplateResolver()->resolveTemplatePath('DummyTemplate')
            ) ?: '',
            'return function() { return \'foo\'; };'
        );
        $this->assertCacheIsNotEmptyForTemplate('DummyTemplate.hbs');

        self::assertSame('foo', $this->subject->render('DummyTemplate'));
    }

    #[Framework\Attributes\Test]
    public function renderLogsCriticalErrorIfTemplateCompilationFails(): void
    {
        self::assertSame(
            '',
            $this->renewSubject(Tests\Unit\Fixtures\Classes\Renderer\DummyRenderer::class)->render('DummyTemplateErroneous')
        );
        self::assertTrue($this->logger->hasCriticalThatPasses(function ($logRecord) {
            /** @var Src\Exception\TemplateCompilationException $exception */
            $exception = $logRecord['context']['exception'];
            self::assertInstanceOf(Src\Exception\TemplateCompilationException::class, $exception);
            self::assertSame(1614620212, $exception->getCode());
            return true;
        }));
    }

    #[Framework\Attributes\Test]
    public function renderDoesNotStoreRenderedTemplateInCacheIfDebugModeIsEnabled(): void
    {
        // Test with TypoScript config.debug = 1
        $this->tsfeMock->config = ['config' => ['debug' => '1']];
        $this->renewSubject()->render('DummyTemplate');
        $this->assertCacheIsEmptyForTemplate('DummyTemplate.hbs');

        // Test with TYPO3_CONF_VARS
        $this->tsfeMock->config = [];
        $GLOBALS['TYPO3_CONF_VARS']['FE']['debug'] = 1;
        $this->renewSubject()->render('DummyTemplate');
        $this->assertCacheIsEmptyForTemplate('DummyTemplate.hbs');
    }

    #[Framework\Attributes\Test]
    public function renderDoesNotStoreRenderedTemplateInCacheIfCachingIsDisabled(): void
    {
        $this->tsfeMock->no_cache = true;
        $this->subject->render('DummyTemplate');
        $this->assertCacheIsEmptyForTemplate('DummyTemplate.hbs');
    }

    #[Framework\Attributes\Test]
    public function renderLogsCriticalErrorIfRenderingClosurePreparationFails(): void
    {
        $this->assertCacheIsEmptyForTemplate('DummyTemplate.hbs');
        $this->getCache()->set(
            file_get_contents(
                $this->getTemplateResolver()->resolveTemplatePath('DummyTemplate')
            ) ?: '',
            'return \'foo\';'
        );
        $this->assertCacheIsNotEmptyForTemplate('DummyTemplate.hbs');

        self::assertSame('', $this->subject->render('DummyTemplate'));
        self::assertTrue($this->logger->hasCriticalThatPasses(function ($logRecord) {
            /** @var Src\Exception\TemplateCompilationException $exception */
            $exception = $logRecord['context']['exception'];
            self::assertInstanceOf(Src\Exception\TemplateCompilationException::class, $exception);
            self::assertSame('Got invalid compile result from compiler.', $exception->getMessage());
            self::assertSame(1639405571, $exception->getCode());
            return true;
        }));
    }

    #[Framework\Attributes\Test]
    public function renderReturnsRenderedTemplate(): void
    {
        self::assertSame(
            'Hello, foo!',
            \trim($this->subject->render('DummyTemplate', ['name' => 'foo']))
        );
    }

    #[Framework\Attributes\Test]
    public function renderResolvesPartialsCorrectlyUsingPartialResolver(): void
    {
        self::assertSame(
            'Hello, foo!' . PHP_EOL . 'Welcome, foo, I am the partial!',
            \trim($this->subject->render('DummyTemplateWithPartial', ['name' => 'foo']))
        );
    }

    #[Framework\Attributes\Test]
    public function resolvePartialReturnsNullIfNoPartialResolverIsRegistered(): void
    {
        $subject = new Src\Renderer\HandlebarsRenderer(
            $this->getCache(),
            new EventDispatcher\EventDispatcher(),
            $this->logger,
            $this->getTemplateResolver(),
        );

        self::assertNull($subject->resolvePartial([], 'foo'));
    }

    #[Framework\Attributes\Test]
    public function resolvePartialThrowsExceptionIfPartialResolverCannotResolveGivenPartial(): void
    {
        $this->expectException(Src\Exception\TemplateNotFoundException::class);
        $this->expectExceptionCode(1606217089);

        $this->subject->resolvePartial([], 'foo');
    }

    #[Framework\Attributes\Test]
    public function resolvePartialResolvesGivenPartialUsingPartialResolver(): void
    {
        self::assertSame(
            'Welcome, {{ name }}, I am the partial!',
            \trim($this->subject->resolvePartial([], 'DummyPartial') ?: '')
        );
    }

    #[Framework\Attributes\Test]
    public function getDefaultDataReturnsDefaultRenderData(): void
    {
        $this->subject->setDefaultData(['foo' => 'baz']);
        self::assertSame(['foo' => 'baz'], $this->subject->getDefaultData());
    }

    private function assertCacheIsEmptyForTemplate(string $template): void
    {
        self::assertNull(
            $this->getCache()->get(file_get_contents($this->templateRootPath . DIRECTORY_SEPARATOR . $template) ?: '')
        );
    }

    private function assertCacheIsNotEmptyForTemplate(string $template): void
    {
        self::assertNotNull(
            $this->getCache()->get(file_get_contents($this->templateRootPath . DIRECTORY_SEPARATOR . $template) ?: '')
        );
    }

    protected function tearDown(): void
    {
        self::assertTrue($this->clearCache(), 'Unable to clear Handlebars cache.');
        parent::tearDown();
    }

    /**
     * @param class-string<Src\Renderer\HandlebarsRenderer> $rendererClass
     */
    private function renewSubject(string $rendererClass = Src\Renderer\HandlebarsRenderer::class): Src\Renderer\HandlebarsRenderer
    {
        $this->logger = new Log\Test\TestLogger();

        return $this->subject = new $rendererClass(
            $this->getCache(),
            new EventDispatcher\EventDispatcher(),
            $this->logger,
            $this->getTemplateResolver(),
            $this->getPartialResolver(),
        );
    }

    private function suppressNextError(int $errorLevels): void
    {
        // Restore error handler on next error
        \set_error_handler(static fn() => \restore_error_handler(), $errorLevels);
    }
}
