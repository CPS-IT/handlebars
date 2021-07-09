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

use Fr\Typo3Handlebars\Exception\InvalidTemplateFileException;
use Fr\Typo3Handlebars\Exception\TemplateCompilationException;
use Fr\Typo3Handlebars\Exception\TemplateNotFoundException;
use Fr\Typo3Handlebars\Renderer\HandlebarsRenderer;
use Fr\Typo3Handlebars\Renderer\Helper\VarDumpHelper;
use Fr\Typo3Handlebars\Tests\Unit\Fixtures\Classes\Renderer\DummyRenderer;
use Fr\Typo3Handlebars\Tests\Unit\Fixtures\Classes\Renderer\Template\DummyTemplateResolver;
use Fr\Typo3Handlebars\Tests\Unit\HandlebarsCacheTrait;
use Fr\Typo3Handlebars\Tests\Unit\HandlebarsTemplateResolverTrait;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\Test\TestLogger;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * HandlebarsRendererTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class HandlebarsRendererTest extends UnitTestCase
{
    use HandlebarsCacheTrait;
    use HandlebarsTemplateResolverTrait;
    use ProphecyTrait;

    /**
     * @var TestLogger
     */
    protected $logger;

    /**
     * @var HandlebarsRenderer
     */
    protected $subject;

    /**
     * @var ObjectProphecy|TypoScriptFrontendController
     */
    protected $tsfeProphecy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->renewSubject();
        $this->tsfeProphecy = $this->prophesize(TypoScriptFrontendController::class);

        $GLOBALS['TSFE'] = $this->tsfeProphecy->reveal();
    }

    /**
     * @test
     */
    public function renderLogsCriticalErrorIfGivenTemplateIsNotAvailable(): void
    {
        self::assertSame('', $this->subject->render('foo.baz'));
        self::assertTrue($this->logger->hasCriticalThatPasses(function ($logRecord) {
            /** @var TemplateNotFoundException $exception */
            $exception = $logRecord['context']['exception'];
            static::assertInstanceOf(TemplateNotFoundException::class, $exception);
            static::assertSame(1606217089, $exception->getCode());
            static::assertStringEndsWith('foo.baz', $exception->getTemplateFile());
            return true;
        }));
    }

    /**
     * @test
     * @
     */
    public function renderLogsCriticalErrorIfGivenTemplateIsNotReadable(): void
    {
        $this->templateResolver = new DummyTemplateResolver();
        $this->suppressNextError(E_WARNING);
        self::assertSame('', $this->renewSubject()->render('foo.baz'));
        self::assertTrue($this->logger->hasCriticalThatPasses(function ($logRecord) {
            /** @var InvalidTemplateFileException $exception */
            $exception = $logRecord['context']['exception'];
            static::assertInstanceOf(InvalidTemplateFileException::class, $exception);
            static::assertSame(1606217313, $exception->getCode());
            static::assertStringEndsWith('foo.baz', $exception->getTemplateFile());
            return true;
        }));
    }

    /**
     * @test
     */
    public function renderUsesGivenTemplatePathIfItIsNotAvailableWithinTemplateRootPaths(): void
    {
        self::assertSame('', $this->subject->render($this->templateRootPath . '/DummyTemplateEmpty'));
    }

    /**
     * @test
     */
    public function renderReturnsEmptyStringIfGivenTemplateIsEmpty(): void
    {
        self::assertSame('', $this->subject->render('DummyTemplateEmpty'));
    }

    /**
     * @test
     */
    public function renderMergesDefaultDataWithGivenData(): void
    {
        $this->subject->registerHelper('varDump', VarDumpHelper::class . '::evaluate');
        $this->subject->setDefaultData([
            'foo' => 'baz',
        ]);

        $expected = print_r([
            'foo' => 'baz',
            'another' => 'foo',
        ], true);
        self::assertSame(
            trim($expected),
            trim($this->subject->render('DummyTemplateVariables', ['another' => 'foo']))
        );
    }

    /**
     * @test
     */
    public function renderUsesCachedCompileResult(): void
    {
        $this->assertCacheIsEmptyForTemplate('DummyTemplate.hbs');
        $this->getCache()->set(
            file_get_contents(
                $this->getTemplateResolver()->resolveTemplatePath('DummyTemplate')
            ),
            'return function() { return \'foo\'; }'
        );
        $this->assertCacheIsNotEmptyForTemplate('DummyTemplate.hbs');

        self::assertSame('foo', $this->subject->render('DummyTemplate'));
    }

    /**
     * @test
     */
    public function renderLogsCriticalErrorIfTemplateCompilationFails(): void
    {
        self::assertSame(
            '',
            $this->renewSubject(DummyRenderer::class)->render('DummyTemplateErroneous')
        );
        self::assertTrue($this->logger->hasCriticalThatPasses(function ($logRecord) {
            /** @var TemplateCompilationException $exception */
            $exception = $logRecord['context']['exception'];
            static::assertInstanceOf(TemplateCompilationException::class, $exception);
            static::assertSame(1614620212, $exception->getCode());
            return true;
        }));
    }

    /**
     * @test
     */
    public function renderDoesNotStoreRenderedTemplateInCacheIfDebugModeIsEnabled(): void
    {
        // Test with TypoScript config.debug = 1
        $this->tsfeProphecy->config = ['config' => ['debug' => '1']];
        $this->renewSubject()->render('DummyTemplate');
        $this->assertCacheIsEmptyForTemplate('DummyTemplate.hbs');

        // Test with TYPO3_CONF_VARS
        $this->tsfeProphecy->config = [];
        $GLOBALS['TYPO3_CONF_VARS']['FE']['debug'] = 1;
        $this->renewSubject()->render('DummyTemplate');
        $this->assertCacheIsEmptyForTemplate('DummyTemplate.hbs');
    }

    /**
     * @test
     */
    public function renderDoesNotStoreRenderedTemplateInCacheIfCachingIsDisabled(): void
    {
        $this->tsfeProphecy->no_cache = true;
        $this->subject->render('DummyTemplate');
        $this->assertCacheIsEmptyForTemplate('DummyTemplate.hbs');
    }

    /**
     * @test
     */
    public function renderLogsCriticalErrorIfRenderingClosurePreparationFails(): void
    {
        $this->assertCacheIsEmptyForTemplate('DummyTemplate.hbs');
        $this->getCache()->set(
            file_get_contents(
                $this->getTemplateResolver()->resolveTemplatePath('DummyTemplate')
            ),
            'return \'foo\';'
        );
        $this->assertCacheIsNotEmptyForTemplate('DummyTemplate.hbs');

        self::assertSame('', $this->subject->render('DummyTemplate'));
        self::assertTrue($this->logger->hasCriticalThatPasses(function ($logRecord) {
            /** @var TemplateCompilationException $exception */
            $exception = $logRecord['context']['exception'];
            static::assertInstanceOf(TemplateCompilationException::class, $exception);
            static::assertSame(1614705397, $exception->getCode());
            return true;
        }));
    }

    /**
     * @test
     */
    public function renderReturnsRenderedTemplate(): void
    {
        self::assertSame(
            'Hello, foo!',
            trim($this->subject->render('DummyTemplate', ['name' => 'foo']))
        );
    }

    /**
     * @test
     */
    public function renderResolvesPartialsCorrectlyUsingPartialResolver(): void
    {
        self::assertSame(
            'Hello, foo!' . PHP_EOL . 'Welcome, foo, I am the partial!',
            trim($this->subject->render('DummyTemplateWithPartial', ['name' => 'foo']))
        );
    }

    /**
     * @test
     */
    public function resolvePartialReturnsNullIfNoPartialResolverIsRegistered(): void
    {
        $subject = new HandlebarsRenderer($this->getCache(), $this->getTemplateResolver());

        self::assertNull($subject->resolvePartial([], 'foo'));
    }

    /**
     * @test
     */
    public function resolvePartialThrowsExceptionIfPartialResolverCannotResolveGivenPartial(): void
    {
        $this->expectException(TemplateNotFoundException::class);
        $this->expectExceptionCode(1606217089);

        $this->subject->resolvePartial([], 'foo');
    }

    /**
     * @test
     */
    public function resolvePartialResolvesGivenPartialUsingPartialResolver(): void
    {
        self::assertSame(
            'Welcome, {{ name }}, I am the partial!',
            trim($this->subject->resolvePartial([], 'DummyPartial'))
        );
    }

    /**
     * @test
     */
    public function getDefaultDataReturnsDefaultRenderData(): void
    {
        $this->subject->setDefaultData(['foo' => 'baz']);
        self::assertSame(['foo' => 'baz'], $this->subject->getDefaultData());
    }

    protected function assertCacheIsEmptyForTemplate(string $template): void
    {
        self::assertNull(
            $this->getCache()->get(file_get_contents($this->templateRootPath . DIRECTORY_SEPARATOR . $template))
        );
    }

    protected function assertCacheIsNotEmptyForTemplate(string $template): void
    {
        self::assertNotNull(
            $this->getCache()->get(file_get_contents($this->templateRootPath . DIRECTORY_SEPARATOR . $template))
        );
    }

    protected function tearDown(): void
    {
        self::assertTrue($this->clearCache(), 'Unable to clear Handlebars cache.');
        parent::tearDown();
    }

    protected function renewSubject(string $rendererClass = HandlebarsRenderer::class): HandlebarsRenderer
    {
        $this->logger = new TestLogger();
        $this->subject = new $rendererClass($this->getCache(), $this->getTemplateResolver(), $this->getPartialResolver());
        $this->subject->setLogger($this->logger);

        return $this->subject;
    }

    protected function suppressNextError(int $errorLevels): void
    {
        /** @phpstan-ignore-next-line */
        set_error_handler(function () {
            // Restore error handler on next error
            restore_error_handler();
        }, $errorLevels);
    }
}
