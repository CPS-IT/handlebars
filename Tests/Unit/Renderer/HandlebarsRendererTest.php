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
    use Tests\FrontendRequestTrait;
    use Tests\HandlebarsCacheTrait;
    use Tests\HandlebarsTemplateResolverTrait;

    private Log\Test\TestLogger $logger;
    private Src\Renderer\Helper\HelperRegistry $helperRegistry;
    private Src\Renderer\HandlebarsRenderer $subject;
    private Frontend\Cache\CacheInstruction $cacheInstruction;
    private Core\TypoScript\FrontendTypoScript $frontendTypoScript;

    protected function setUp(): void
    {
        parent::setUp();

        $this->renewSubject();
        $this->buildServerRequest($cacheInstruction, $frontendTypoScript);

        $this->cacheInstruction = $cacheInstruction;
        $this->frontendTypoScript = $frontendTypoScript;
    }

    #[Framework\Attributes\Test]
    public function renderThrowsExceptionIfTemplateCompilationFails(): void
    {
        $view = new Src\Renderer\Template\View\HandlebarsView('DummyTemplateErroneous');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/^Wrong variable naming as \'baz!\'/');

        $this->renewSubject(Tests\Unit\Fixtures\Classes\Renderer\DummyRenderer::class)->render($view);
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
array (2 items)
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
        $view->assign('name', 'foo');

        // Test with TypoScript config.debug = 1
        $this->frontendTypoScript->setConfigArray(['debug' => '1']);
        $this->renewSubject()->render($view);
        $this->assertCacheIsEmptyForTemplate('DummyTemplate.hbs');

        // Test with TYPO3_CONF_VARS
        $this->frontendTypoScript->setConfigArray([]);
        $GLOBALS['TYPO3_CONF_VARS']['FE']['debug'] = 1;
        $this->renewSubject()->render($view);
        $this->assertCacheIsEmptyForTemplate('DummyTemplate.hbs');
    }

    #[Framework\Attributes\Test]
    public function renderDoesNotStoreRenderedTemplateInCacheIfCachingIsDisabled(): void
    {
        $view = new Src\Renderer\Template\View\HandlebarsView('DummyTemplate');

        $this->cacheInstruction->disableCache('testing');

        $this->subject->render($view);

        $this->assertCacheIsEmptyForTemplate('DummyTemplate.hbs');
    }

    #[Framework\Attributes\Test]
    public function renderThrowsExceptionOnErrorIfDebugModeIsEnabled(): void
    {
        $view = new Src\Renderer\Template\View\HandlebarsView('DummyTemplate');

        $this->frontendTypoScript->setConfigArray(['debug' => '1']);

        $this->expectExceptionObject(
            new \Exception('Runtime: [name] does not exist'),
        );

        $this->renewSubject()->render($view);
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
    public function renderThrowsExceptionIfPartialCannotBeResolved(): void
    {
        $this->templateResolver = new Tests\Unit\Fixtures\Classes\Renderer\Template\DummyInvalidTemplateResolver();
        $this->templateResolver->templateMap = [
            'DummyTemplateWithPartial' => \dirname(__DIR__) . '/Fixtures/Templates/DummyTemplateWithPartial.hbs',
        ];

        $view = new Src\Renderer\Template\View\HandlebarsView('DummyTemplateWithPartial', ['name' => 'foo']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The partial DummyPartial could not be found');

        $this->renewSubject()->render($view);
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
