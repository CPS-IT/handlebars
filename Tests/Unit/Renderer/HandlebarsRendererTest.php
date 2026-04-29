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
use Symfony\Component\EventDispatcher;
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
        $context = new Src\Renderer\RenderingContext('DummyTemplateErroneous');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/^Parse error on line 1/');

        $this->renewSubject(Tests\Unit\Fixtures\Classes\Renderer\DummyRenderer::class)->render($context);
    }

    #[Framework\Attributes\Test]
    public function renderThrowsExceptionIfTemplateFilsIsInvalid(): void
    {
        $context = new Src\Renderer\RenderingContext('foo.baz');

        $this->templateResolver = new Tests\Unit\Fixtures\Classes\Renderer\Template\DummyTemplateResolver();

        $this->expectException(Src\Exception\TemplateFileIsInvalid::class);
        $this->expectExceptionMessageMatches('/^The template file "[^"]+\/foo\.baz" is invalid or does not exist\.$/');
        $this->expectExceptionCode(1736333208);

        $this->renewSubject()->render($context);
    }

    #[Framework\Attributes\Test]
    public function renderThrowsExceptionIfTemplatePathIsNotResolvable(): void
    {
        $context = new Src\Renderer\RenderingContext('foo.baz');

        $this->expectExceptionObject(
            new Src\Exception\TemplatePathIsNotResolvable('foo.baz')
        );

        $this->renewSubject()->render($context);
    }

    #[Framework\Attributes\Test]
    public function renderThrowsExceptionIfGivenViewIsNotProperlyInitialized(): void
    {
        $context = new Src\Renderer\RenderingContext();

        $this->expectExceptionObject(
            new Src\Exception\ViewIsNotProperlyInitialized(),
        );

        $this->subject->render($context);
    }

    #[Framework\Attributes\Test]
    public function renderUsesGivenTemplatePathIfItIsNotAvailableWithinTemplateRootPaths(): void
    {
        $context = new Src\Renderer\RenderingContext($this->templateRootPath . '/DummyTemplateEmpty');

        self::assertSame('', $this->subject->render($context));
    }

    #[Framework\Attributes\Test]
    public function renderReturnsEmptyStringIfGivenTemplateIsEmpty(): void
    {
        $context = new Src\Renderer\RenderingContext('DummyTemplateEmpty');

        self::assertSame('', $this->subject->render($context));
    }

    #[Framework\Attributes\Test]
    public function renderMergesVariablesWithGivenVariables(): void
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
            trim($this->subject->render($context)),
        );
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

        $context = new Src\Renderer\RenderingContext('DummyTemplate');

        self::assertSame('foo', $this->subject->render($context));
    }

    #[Framework\Attributes\Test]
    public function renderDoesNotStoreRenderedTemplateInCacheIfDebugModeIsEnabled(): void
    {
        $context = new Src\Renderer\RenderingContext('DummyTemplate');
        $context->assign('name', 'foo');

        // Test with TypoScript config.debug = 1
        $this->frontendTypoScript->setConfigArray(['debug' => '1']);
        $this->renewSubject()->render($context);
        $this->assertCacheIsEmptyForTemplate('DummyTemplate.hbs');

        // Test with TYPO3_CONF_VARS
        $this->frontendTypoScript->setConfigArray([]);
        $GLOBALS['TYPO3_CONF_VARS']['FE']['debug'] = 1;
        $this->renewSubject()->render($context);
        $this->assertCacheIsEmptyForTemplate('DummyTemplate.hbs');
    }

    #[Framework\Attributes\Test]
    public function renderDoesNotStoreRenderedTemplateInCacheIfCachingIsDisabled(): void
    {
        $context = new Src\Renderer\RenderingContext('DummyTemplate');

        $this->cacheInstruction->disableCache('testing');

        $this->subject->render($context);

        $this->assertCacheIsEmptyForTemplate('DummyTemplate.hbs');
    }

    #[Framework\Attributes\Test]
    public function renderThrowsExceptionOnErrorIfDebugModeIsEnabled(): void
    {
        $context = new Src\Renderer\RenderingContext('DummyTemplate');

        $this->frontendTypoScript->setConfigArray(['debug' => '1']);

        $this->expectExceptionObject(
            new \Exception('"name" not defined'),
        );

        $this->renewSubject()->render($context);
    }

    #[Framework\Attributes\Test]
    public function renderReturnsRenderedTemplate(): void
    {
        $context = new Src\Renderer\RenderingContext('DummyTemplate', ['name' => 'foo']);

        self::assertSame(
            'Hello, foo!',
            trim($this->subject->render($context)),
        );
    }

    #[Framework\Attributes\Test]
    public function renderResolvesPartialsCorrectlyUsingPartialResolver(): void
    {
        $context = new Src\Renderer\RenderingContext('DummyTemplateWithPartial', ['name' => 'foo']);

        self::assertSame(
            'Hello, foo!' . PHP_EOL . 'Welcome, foo, I am the partial!',
            trim($this->subject->render($context)),
        );
    }

    #[Framework\Attributes\Test]
    public function renderThrowsExceptionIfPartialCannotBeResolved(): void
    {
        $this->templateResolver = new Tests\Unit\Fixtures\Classes\Renderer\Template\DummyInvalidTemplateResolver();
        $this->templateResolver->templateMap = [
            'DummyTemplateWithPartial' => dirname(__DIR__) . '/Fixtures/Templates/DummyTemplateWithPartial.hbs',
        ];

        $context = new Src\Renderer\RenderingContext('DummyTemplateWithPartial', ['name' => 'foo']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The partial DummyPartial could not be found');

        $this->renewSubject()->render($context);
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

        $actual = $this->subject->render($context);

        self::assertSame('Welcome, foo, I am the partial!', trim($actual));
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
        $this->helperRegistry = new Src\Renderer\Helper\HelperRegistry(new Log\Test\TestLogger());

        return $this->subject = new $rendererClass(
            $this->getCache(),
            new EventDispatcher\EventDispatcher(),
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
