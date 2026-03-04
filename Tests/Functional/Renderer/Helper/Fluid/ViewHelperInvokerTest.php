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

namespace CPSIT\Typo3Handlebars\Tests\Functional\Renderer\Helper\Fluid;

use CPSIT\Typo3Handlebars as Src;
use CPSIT\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use Psr\Http\Message;
use Psr\Log;
use Symfony\Component\EventDispatcher;
use TYPO3\CMS\Fluid;
use TYPO3\TestingFramework;

/**
 * ViewHelperInvokerTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Helper\Fluid\ViewHelperInvoker::class)]
final class ViewHelperInvokerTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\FrontendRequestTrait;
    use Tests\HandlebarsTemplateResolverTrait;

    protected array $testExtensionsToLoad = [
        'handlebars',
        'test_extension',
    ];

    private Log\Test\TestLogger $logger;
    private Src\Renderer\HandlebarsRenderer $renderer;
    private Message\ServerRequestInterface $request;

    protected function setUp(): void
    {
        $this->allowAdditionalRootPaths();

        parent::setUp();

        $helperRegistry = new Src\Renderer\Helper\HelperRegistry(new Log\NullLogger());

        $this->templateRootPath = 'EXT:test_extension/Resources/Templates/';
        $this->logger = new Log\Test\TestLogger();
        $this->templateResolver = new Src\Renderer\Template\FlatTemplateResolver($this->getTemplatePaths());
        $this->renderer = new Src\Renderer\HandlebarsRenderer(
            new Src\Cache\NullCache(),
            new EventDispatcher\EventDispatcher(),
            $helperRegistry,
            $this->templateResolver,
            new Src\Renderer\Variables\VariableBag([]),
        );

        $subject = new Src\Renderer\Helper\Fluid\ViewHelperInvoker(
            $this->get(Fluid\Core\Rendering\RenderingContextFactory::class),
            $this->logger,
        );

        $helperRegistry->add('viewHelper', $subject);
        $helperRegistry->add('viewHelperNamespace', $subject->registerNamespace(...));

        $this->request = $this->buildServerRequest();
    }

    #[Framework\Attributes\Test]
    public function renderLogsErrorOnInvalidViewHelperName(): void
    {
        $this->renderer->render(
            new Src\Renderer\RenderingContext('@viewHelper-invalid', [], $this->request),
        );

        self::assertTrue(
            $this->logger->hasError([
                'message' => 'Fluid ViewHelper invoker requires a valid combination of namespace and view helper shortname, e.g. "f:debug", "{name}" given.',
                'context' => [
                    'name' => 'foo',
                ],
            ])
        );
    }

    #[Framework\Attributes\Test]
    public function renderDelegatesRenderingToRequestedViewHelper(): void
    {
        $actual = $this->renderer->render(
            new Src\Renderer\RenderingContext(
                '@viewHelper',
                [
                    'array' => ['foo', 'baz'],
                ],
                $this->request,
            ),
        );

        self::assertSame('foo.baz', trim($actual));
    }

    #[Framework\Attributes\Test]
    public function registerNamespaceDoesNothingIfNamespacesVariablesWasModifiedOutside(): void
    {
        $this->expectExceptionMessageMatches('/No suitable resolvers were registered for this namespace/');

        $this->renderer->render(
            new Src\Renderer\RenderingContext(
                '@viewHelperNamespace',
                [
                    '_namespaces' => 'foo',
                ],
                $this->request,
            ),
        );
    }

    #[Framework\Attributes\Test]
    public function registerNamespaceAddsGivenLocalNamespaceToRenderingContext(): void
    {
        $renderingContext = new Src\Renderer\RenderingContext('@viewHelperNamespace');

        $expected = [
            '_namespaces' => [
                'test' => [
                    'CPSIT\\Typo3Handlebars\\TestExtension\\ViewHelpers',
                ],
            ],
        ];

        $actual = $this->renderer->render($renderingContext);

        self::assertSame('bar', trim($actual));
        self::assertSame($expected, $renderingContext->getVariables());
    }

    #[Framework\Attributes\Test]
    public function registerNamespaceMergesMultipleLocalNamespaces(): void
    {
        $renderingContext = new Src\Renderer\RenderingContext(
            '@viewHelperNamespace-multiple',
            [
                '_namespaces' => [
                    'test' => [
                        'CPSIT\\Typo3Handlebars\\TestExtension\\ViewHelpers',
                    ],
                ],
            ],
        );

        $expected = [
            '_namespaces' => [
                'test' => [
                    'CPSIT\\Typo3Handlebars\\TestExtension\\ViewHelpers',
                    'CPSIT\\Typo3Handlebars\\TestExtension\\ViewHelpers\\Other',
                    'CPSIT\\Typo3Handlebars\\TestExtension\\ViewHelpers\\Other\\Other',
                ],
            ],
        ];

        $actual = $this->renderer->render($renderingContext);

        self::assertSame('bar', trim($actual));
        self::assertSame($expected, $renderingContext->getVariables());
    }
}
