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
    private Src\Renderer\Helper\HelperRegistry $helperRegistry;
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
    public function renderLogsCriticalErrorIfTemplateCompilationFails(): void
    {
        $view = new Src\Renderer\Template\View\HandlebarsView('DummyTemplateErroneous');

        self::assertSame(
            '',
            $this->renewSubject(Tests\Unit\Fixtures\Classes\Renderer\DummyRenderer::class)->render($view),
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
    public function renderLogsCriticalErrorIfTemplateFilsIsInvalid(): void
    {
        $view = new Src\Renderer\Template\View\HandlebarsView('foo.baz');

        $this->templateResolver = new Tests\Unit\Fixtures\Classes\Renderer\Template\DummyTemplateResolver();

        $this->suppressNextError(E_WARNING);

        self::assertSame('', $this->renewSubject()->render($view));
        self::assertTrue($this->logger->hasCriticalThatPasses(function ($logRecord) {
            $exception = $logRecord['context']['exception'];

            self::assertInstanceOf(Src\Exception\TemplateFileIsInvalid::class, $exception);
            self::assertSame(1736333208, $exception->getCode());
            self::assertMatchesRegularExpression(
                '/^The template file "[^"]+\/foo\.baz" is invalid or does not exist\.$/',
                $exception->getMessage(),
            );

            return true;
        }));
    }

    #[Framework\Attributes\Test]
    public function renderLogsCriticalErrorIfTemplatePathIsNotResolvable(): void
    {
        $view = new Src\Renderer\Template\View\HandlebarsView('foo.baz');

        self::assertSame('', $this->subject->render($view));
        self::assertTrue($this->logger->hasCriticalThatPasses(function ($logRecord) {
            /** @var Src\Exception\TemplatePathIsNotResolvable $exception */
            $exception = $logRecord['context']['exception'];
            self::assertInstanceOf(Src\Exception\TemplatePathIsNotResolvable::class, $exception);
            self::assertSame('The template path "foo.baz" cannot be resolved.', $exception->getMessage());
            self::assertSame(1736254772, $exception->getCode());
            return true;
        }));
    }

    #[Framework\Attributes\Test]
    public function renderLogsCriticalErrorIfGivenViewIsNotProperlyInitialized(): void
    {
        $view = new Src\Renderer\Template\View\HandlebarsView();

        self::assertSame('', $this->subject->render($view));
        self::assertTrue($this->logger->hasCriticalThatPasses(function ($logRecord) {
            /** @var Src\Exception\ViewIsNotProperlyInitialized $exception */
            $exception = $logRecord['context']['exception'];

            self::assertInstanceOf(Src\Exception\ViewIsNotProperlyInitialized::class, $exception);
            self::assertSame(
                'The Handlebars view is not properly initialized. Provide either template path or template source.',
                $exception->getMessage(),
            );
            self::assertSame(1736332788, $exception->getCode());

            return true;
        }));
    }

    #[Framework\Attributes\Test]
    public function renderUsesGivenTemplatePathIfItIsNotAvailableWithinTemplateRootPaths(): void
    {
        $view = new Src\Renderer\Template\View\HandlebarsView($this->templateRootPath . '/DummyTemplateEmpty');

        self::assertSame('', $this->subject->render($view));
    }

    #[Framework\Attributes\Test]
    public function renderReturnsEmptyStringIfGivenTemplateIsEmpty(): void
    {
        $view = new Src\Renderer\Template\View\HandlebarsView('DummyTemplateEmpty');

        self::assertSame('', $this->subject->render($view));
    }

    #[Framework\Attributes\Test]
    public function renderMergesVariablesWithGivenVariables(): void
    {
        $this->helperRegistry->add('varDump', Src\Renderer\Helper\VarDumpHelper::class);

        Core\Utility\DebugUtility::useAnsiColor(false);

        $expected = <<<EOF
Debug
array(2 items)
   foo => "baz" (3 chars)
   another => "foo" (3 chars)
EOF;

        $view = new Src\Renderer\Template\View\HandlebarsView('DummyTemplateVariables', ['another' => 'foo']);

        self::assertSame(
            \trim($expected),
            \trim($this->subject->render($view)),
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

        $view = new Src\Renderer\Template\View\HandlebarsView('DummyTemplate');

        self::assertSame('foo', $this->subject->render($view));
    }

    #[Framework\Attributes\Test]
    public function renderDoesNotStoreRenderedTemplateInCacheIfDebugModeIsEnabled(): void
    {
        $view = new Src\Renderer\Template\View\HandlebarsView('DummyTemplate');

        // Test with TypoScript config.debug = 1
        $this->tsfeMock->config = ['config' => ['debug' => '1']];
        $this->renewSubject()->render($view);
        $this->assertCacheIsEmptyForTemplate('DummyTemplate.hbs');

        // Test with TYPO3_CONF_VARS
        $this->tsfeMock->config = [];
        $GLOBALS['TYPO3_CONF_VARS']['FE']['debug'] = 1;
        $this->renewSubject()->render($view);
        $this->assertCacheIsEmptyForTemplate('DummyTemplate.hbs');
    }

    #[Framework\Attributes\Test]
    public function renderDoesNotStoreRenderedTemplateInCacheIfCachingIsDisabled(): void
    {
        $view = new Src\Renderer\Template\View\HandlebarsView('DummyTemplate');

        $this->tsfeMock->no_cache = true;
        $this->subject->render($view);

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

        $view = new Src\Renderer\Template\View\HandlebarsView('DummyTemplate');

        self::assertSame('', $this->subject->render($view));
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
        $view = new Src\Renderer\Template\View\HandlebarsView('DummyTemplate', ['name' => 'foo']);

        self::assertSame(
            'Hello, foo!',
            \trim($this->subject->render($view)),
        );
    }

    #[Framework\Attributes\Test]
    public function renderResolvesPartialsCorrectlyUsingPartialResolver(): void
    {
        $view = new Src\Renderer\Template\View\HandlebarsView('DummyTemplateWithPartial', ['name' => 'foo']);

        self::assertSame(
            'Hello, foo!' . PHP_EOL . 'Welcome, foo, I am the partial!',
            \trim($this->subject->render($view)),
        );
    }

    #[Framework\Attributes\Test]
    public function resolvePartialThrowsExceptionIfPartialResolverCannotResolveGivenPartial(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\PartialPathIsNotResolvable('foo'),
        );

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
        $this->helperRegistry = new Src\Renderer\Helper\HelperRegistry($this->logger);

        return $this->subject = new $rendererClass(
            $this->getCache(),
            new EventDispatcher\EventDispatcher(),
            $this->helperRegistry,
            $this->logger,
            $this->getTemplateResolver(),
            new Src\Renderer\Variables\VariableBag([
                new Src\Renderer\Variables\GlobalVariableProvider([
                    'foo' => 'baz',
                ]),
            ]),
        );
    }

    private function suppressNextError(int $errorLevels): void
    {
        // Restore error handler on next error
        \set_error_handler(static fn() => \restore_error_handler(), $errorLevels);
    }
}
