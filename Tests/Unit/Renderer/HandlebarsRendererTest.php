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

namespace CPSIT\Typo3Handlebars\Tests\Unit\Renderer;

use CPSIT\Typo3Handlebars as Src;
use CPSIT\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use Psr\Log;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
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

    private Tests\Unit\Fixtures\Classes\DummyEventDispatcher $eventDispatcher;
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
    public function renderTemplateThrowsExceptionIfTemplateCompilationFails(): void
    {
        $context = new Src\Renderer\RenderingContext('DummyTemplateErroneous');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/^Parse error on line 1/');

        $this->renewSubject(Tests\Unit\Fixtures\Classes\Renderer\DummyRenderer::class)->renderTemplate($context);
    }

    #[Framework\Attributes\Test]
    public function renderTemplateThrowsExceptionIfTemplateFilsIsInvalid(): void
    {
        $context = new Src\Renderer\RenderingContext('foo.baz');

        $this->templateResolver = new Tests\Unit\Fixtures\Classes\Renderer\Template\DummyTemplateResolver();

        $this->expectException(Src\Exception\TemplateFileIsInvalid::class);
        $this->expectExceptionMessageMatches('/^The template file "[^"]+\/foo\.baz" is invalid or does not exist\.$/');
        $this->expectExceptionCode(1736333208);

        $this->renewSubject()->renderTemplate($context);
    }

    #[Framework\Attributes\Test]
    public function renderTemplateThrowsExceptionIfTemplatePathIsNotResolvable(): void
    {
        $context = new Src\Renderer\RenderingContext('foo.baz');

        $this->expectExceptionObject(
            new Src\Exception\TemplatePathIsNotResolvable('foo.baz')
        );

        $this->renewSubject()->renderTemplate($context);
    }

    #[Framework\Attributes\Test]
    public function renderTemplateThrowsExceptionIfGivenViewIsNotProperlyInitialized(): void
    {
        $context = new Src\Renderer\RenderingContext();

        $this->expectExceptionObject(
            new Src\Exception\ViewIsNotProperlyInitialized(),
        );

        $this->subject->renderTemplate($context);
    }

    #[Framework\Attributes\Test]
    public function renderTemplateUsesGivenTemplatePathIfItIsNotAvailableWithinTemplateRootPaths(): void
    {
        $context = new Src\Renderer\RenderingContext($this->templateRootPath . '/DummyTemplateEmpty');

        self::assertSame('', $this->subject->renderTemplate($context));
    }

    #[Framework\Attributes\Test]
    public function renderTemplateReturnsEmptyStringIfGivenTemplateIsEmpty(): void
    {
        $context = new Src\Renderer\RenderingContext('DummyTemplateEmpty');

        self::assertSame('', $this->subject->renderTemplate($context));
    }

    #[Framework\Attributes\Test]
    public function renderTemplateMergesVariablesWithGivenVariables(): void
    {
        $this->helperRegistry->add('debug', Src\Renderer\Helper\DebugHelper::class);

        // Pre-render var_dump, because the first call contains stylesheet, whereas following calls don't
        $this->renderVarDump(null);

        $expected = $this->renderVarDump([
            'foo' => 'baz',
            'another' => 'foo',
        ]);

        $context = new Src\Renderer\RenderingContext('DummyTemplateVariables', ['another' => 'foo']);

        self::assertSame(
            trim($expected),
            trim($this->subject->renderTemplate($context)),
        );
    }

    #[Framework\Attributes\Test]
    public function renderTemplateUsesCachedCompileResult(): void
    {
        $this->assertCacheIsEmptyForTemplate('DummyTemplate.hbs');
        $this->getCache()->set(
            file_get_contents(
                $this->getTemplateResolver()->resolveTemplatePath('DummyTemplate')
            ) ?: '',
            'return function() { return \'foo\'; };'
        );
        $this->assertCacheIsNotEmptyForTemplate('DummyTemplate.hbs');

        $context = new Src\Renderer\RenderingContext('DummyTemplate');

        self::assertSame('foo', $this->subject->renderTemplate($context));
    }

    #[Framework\Attributes\Test]
    public function renderTemplateDoesNotStoreRenderedTemplateInCacheIfDebugModeIsEnabled(): void
    {
        $context = new Src\Renderer\RenderingContext('DummyTemplate');
        $context->assign('name', 'foo');

        // Test with TypoScript config.debug = 1
        $this->frontendTypoScript->setConfigArray(['debug' => '1']);
        $this->renewSubject()->renderTemplate($context);
        $this->assertCacheIsEmptyForTemplate('DummyTemplate.hbs');

        // Test with TYPO3_CONF_VARS
        $this->frontendTypoScript->setConfigArray([]);
        $GLOBALS['TYPO3_CONF_VARS']['FE']['debug'] = 1;
        $this->renewSubject()->renderTemplate($context);
        $this->assertCacheIsEmptyForTemplate('DummyTemplate.hbs');
    }

    #[Framework\Attributes\Test]
    public function renderTemplateDoesNotStoreRenderedTemplateInCacheIfCachingIsDisabled(): void
    {
        $context = new Src\Renderer\RenderingContext('DummyTemplate');

        $this->cacheInstruction->disableCache('testing');

        $this->subject->renderTemplate($context);

        $this->assertCacheIsEmptyForTemplate('DummyTemplate.hbs');
    }

    #[Framework\Attributes\Test]
    public function renderTemplateThrowsExceptionOnErrorIfDebugModeIsEnabled(): void
    {
        $context = new Src\Renderer\RenderingContext('DummyTemplate');

        $this->frontendTypoScript->setConfigArray(['debug' => '1']);

        $this->expectExceptionObject(
            new \Exception('"name" not defined'),
        );

        $this->renewSubject()->renderTemplate($context);
    }

    #[Framework\Attributes\Test]
    public function renderTemplateReturnsRenderedTemplate(): void
    {
        $context = new Src\Renderer\RenderingContext('DummyTemplate', ['name' => 'foo']);

        self::assertSame(
            'Hello, foo!',
            trim($this->subject->renderTemplate($context)),
        );
    }

    #[Framework\Attributes\Test]
    public function renderTemplateResolvesPartialsCorrectlyUsingPartialResolver(): void
    {
        $context = new Src\Renderer\RenderingContext('DummyTemplateWithPartial', ['name' => 'foo']);

        self::assertSame(
            'Hello, foo!' . PHP_EOL . 'Welcome, foo, I am the partial!',
            trim($this->subject->renderTemplate($context)),
        );
    }

    #[Framework\Attributes\Test]
    public function renderTemplateThrowsExceptionIfPartialCannotBeResolved(): void
    {
        $this->templateResolver = new Tests\Unit\Fixtures\Classes\Renderer\Template\DummyInvalidTemplateResolver();
        $this->templateResolver->templateMap = [
            'DummyTemplateWithPartial' => dirname(__DIR__) . '/Fixtures/Templates/DummyTemplateWithPartial.hbs',
        ];

        $context = new Src\Renderer\RenderingContext('DummyTemplateWithPartial', ['name' => 'foo']);

        $this->expectExceptionObject(
            new Src\Exception\PartialPathIsNotResolvable('DummyPartial'),
        );

        $this->renewSubject()->renderTemplate($context);
    }

    #[Framework\Attributes\Test]
    public function renderTemplateDispatchesEvents(): void
    {
        $context = new Src\Renderer\RenderingContext('DummyTemplateVariables', ['another' => 'foo']);

        $this->subject->renderTemplate($context);

        self::assertCount(3, $this->eventDispatcher->dispatchedEvents);
        self::assertInstanceOf(
            Src\Event\BeforeTemplateCompilationEvent::class,
            $this->eventDispatcher->dispatchedEvents[0],
        );
        self::assertInstanceOf(
            Src\Event\BeforeRenderingEvent::class,
            $this->eventDispatcher->dispatchedEvents[1],
        );
        self::assertInstanceOf(
            Src\Event\AfterRenderingEvent::class,
            $this->eventDispatcher->dispatchedEvents[2],
        );
    }

    #[Framework\Attributes\Test]
    public function renderPartialThrowsExceptionIfPartialCompilationFails(): void
    {
        $context = new Src\Renderer\RenderingContext('DummyPartialErroneous');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/^Parse error on line 1/');

        $this->renewSubject(Tests\Unit\Fixtures\Classes\Renderer\DummyRenderer::class)->renderPartial($context);
    }

    #[Framework\Attributes\Test]
    public function renderPartialThrowsExceptionIfPartialFilsIsInvalid(): void
    {
        $context = new Src\Renderer\RenderingContext('foo.baz');

        $this->templateResolver = new Tests\Unit\Fixtures\Classes\Renderer\Template\DummyTemplateResolver();

        $this->expectException(Src\Exception\TemplateFileIsInvalid::class);
        $this->expectExceptionMessageMatches('/^The template file "[^"]+\/foo\.baz" is invalid or does not exist\.$/');
        $this->expectExceptionCode(1736333208);

        $this->renewSubject()->renderPartial($context);
    }

    #[Framework\Attributes\Test]
    public function renderPartialThrowsExceptionIfPartialPathIsNotResolvable(): void
    {
        $context = new Src\Renderer\RenderingContext('foo.baz');

        $this->expectExceptionObject(
            new Src\Exception\PartialPathIsNotResolvable('foo.baz')
        );

        $this->renewSubject()->renderPartial($context);
    }

    #[Framework\Attributes\Test]
    public function renderPartialThrowsExceptionIfGivenViewIsNotProperlyInitialized(): void
    {
        $context = new Src\Renderer\RenderingContext();

        $this->expectExceptionObject(
            new Src\Exception\ViewIsNotProperlyInitialized(),
        );

        $this->subject->renderPartial($context);
    }

    #[Framework\Attributes\Test]
    public function renderPartialUsesGivenPartialPathIfItIsNotAvailableWithinPartialRootPaths(): void
    {
        $context = new Src\Renderer\RenderingContext($this->partialRootPath . '/DummyPartialEmpty');

        self::assertSame('', $this->subject->renderPartial($context));
    }

    #[Framework\Attributes\Test]
    public function renderPartialReturnsEmptyStringIfGivenPartialIsEmpty(): void
    {
        $context = new Src\Renderer\RenderingContext('DummyPartialEmpty');

        self::assertSame('', $this->subject->renderPartial($context));
    }

    #[Framework\Attributes\Test]
    public function renderPartialMergesVariablesWithGivenVariables(): void
    {
        $this->helperRegistry->add('debug', Src\Renderer\Helper\DebugHelper::class);

        // Pre-render var_dump, because the first call contains stylesheet, whereas following calls don't
        $this->renderVarDump(null);

        $expected = $this->renderVarDump([
            'foo' => 'baz',
            'another' => 'foo',
        ]);

        $context = new Src\Renderer\RenderingContext('DummyPartialVariables', ['another' => 'foo']);

        self::assertSame(
            trim($expected),
            trim($this->subject->renderPartial($context)),
        );
    }

    #[Framework\Attributes\Test]
    public function renderPartialUsesCachedCompileResult(): void
    {
        $this->assertCacheIsEmptyForTemplate('DummyPartial.hbs', true);
        $this->getCache()->set(
            file_get_contents(
                $this->getTemplateResolver()->resolvePartialPath('DummyPartial')
            ) ?: '',
            'return function() { return \'foo\'; };'
        );
        $this->assertCacheIsNotEmptyForTemplate('DummyPartial.hbs', true);

        $context = new Src\Renderer\RenderingContext('DummyPartial');

        self::assertSame('foo', $this->subject->renderPartial($context));
    }

    #[Framework\Attributes\Test]
    public function renderPartialDoesNotStoreRenderedPartialInCacheIfDebugModeIsEnabled(): void
    {
        $context = new Src\Renderer\RenderingContext('DummyPartial');
        $context->assign('name', 'foo');

        // Test with TypoScript config.debug = 1
        $this->frontendTypoScript->setConfigArray(['debug' => '1']);
        $this->renewSubject()->renderPartial($context);
        $this->assertCacheIsEmptyForTemplate('DummyPartial.hbs', true);

        // Test with TYPO3_CONF_VARS
        $this->frontendTypoScript->setConfigArray([]);
        $GLOBALS['TYPO3_CONF_VARS']['FE']['debug'] = 1;
        $this->renewSubject()->renderPartial($context);
        $this->assertCacheIsEmptyForTemplate('DummyPartial.hbs', true);
    }

    #[Framework\Attributes\Test]
    public function renderPartialDoesNotStoreRenderedPartialInCacheIfCachingIsDisabled(): void
    {
        $context = new Src\Renderer\RenderingContext('DummyPartial');

        $this->cacheInstruction->disableCache('testing');

        $this->subject->renderPartial($context);

        $this->assertCacheIsEmptyForTemplate('DummyPartial.hbs', true);
    }

    #[Framework\Attributes\Test]
    public function renderPartialThrowsExceptionOnErrorIfDebugModeIsEnabled(): void
    {
        $context = new Src\Renderer\RenderingContext('DummyPartial');

        $this->frontendTypoScript->setConfigArray(['debug' => '1']);

        $this->expectExceptionObject(
            new \Exception('"name" not defined'),
        );

        $this->renewSubject()->renderPartial($context);
    }

    #[Framework\Attributes\Test]
    public function renderPartialReturnsRenderedPartial(): void
    {
        $context = new Src\Renderer\RenderingContext('DummyPartial', ['name' => 'foo']);

        self::assertSame(
            'Welcome, foo, I am the partial!',
            trim($this->subject->renderPartial($context)),
        );
    }

    #[Framework\Attributes\Test]
    public function renderPartialDispatchesEvents(): void
    {
        $context = new Src\Renderer\RenderingContext('DummyPartial', ['name' => 'foo']);

        $this->subject->renderPartial($context);

        self::assertCount(3, $this->eventDispatcher->dispatchedEvents);
        self::assertInstanceOf(
            Src\Event\BeforeTemplateCompilationEvent::class,
            $this->eventDispatcher->dispatchedEvents[0],
        );
        self::assertInstanceOf(
            Src\Event\BeforeRenderingEvent::class,
            $this->eventDispatcher->dispatchedEvents[1],
        );
        self::assertInstanceOf(
            Src\Event\AfterRenderingEvent::class,
            $this->eventDispatcher->dispatchedEvents[2],
        );
    }

    /**
     * This is a test case for the specific {{> (lookup)}} behavior, which is natively baked
     * into the devtheorem/php-handlebars library. It's just here to verify this behavior
     * still works, as it is heavily used in our custom projects.
     */
    #[Framework\Attributes\Test]
    public function lookupResolvesPartialNameFromSubExpression(): void
    {
        $context = new Src\Renderer\RenderingContext();
        $context->setTemplateSource("{{> (lookup . 'templateName')}}");
        $context->assign('templateName', 'DummyPartial');
        $context->assign('name', 'foo');

        $actual = $this->subject->renderTemplate($context);

        self::assertSame('Welcome, foo, I am the partial!', trim($actual));
    }

    private function assertCacheIsEmptyForTemplate(string $template, bool $partial = false): void
    {
        $rootPath = $partial ? $this->partialRootPath : $this->templateRootPath;

        self::assertNull(
            $this->getCache()->get(file_get_contents($rootPath . DIRECTORY_SEPARATOR . $template) ?: '')
        );
    }

    private function assertCacheIsNotEmptyForTemplate(string $template, bool $partial = false): void
    {
        $rootPath = $partial ? $this->partialRootPath : $this->templateRootPath;

        self::assertNotNull(
            $this->getCache()->get(file_get_contents($rootPath . DIRECTORY_SEPARATOR . $template) ?: '')
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
        $this->eventDispatcher = new Tests\Unit\Fixtures\Classes\DummyEventDispatcher();
        $this->helperRegistry = new Src\Renderer\Helper\HelperRegistry(new Log\Test\TestLogger());

        return $this->subject = new $rendererClass(
            $this->getCache(),
            $this->eventDispatcher,
            $this->helperRegistry,
            $this->getTemplateResolver(),
            new Src\Renderer\Variables\VariableBag([
                new Src\Renderer\Variables\GlobalVariableProvider([
                    'foo' => 'baz',
                ]),
            ]),
        );
    }

    private function renderVarDump(mixed $subject): string
    {
        return Extbase\Utility\DebuggerUtility::var_dump(
            $subject,
            'Debug',
            12,
            false,
            false,
            true,
        );
    }
}
