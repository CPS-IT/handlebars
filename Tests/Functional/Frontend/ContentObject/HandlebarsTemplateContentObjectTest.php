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

namespace CPSIT\Typo3Handlebars\Tests\Functional\Frontend\ContentObject;

use CPSIT\Typo3Handlebars as Src;
use CPSIT\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Frontend;
use TYPO3\TestingFramework;

/**
 * HandlebarsTemplateContentObjectTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Frontend\ContentObject\HandlebarsTemplateContentObject::class)]
final class HandlebarsTemplateContentObjectTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\FrontendRequestTrait;

    protected array $testExtensionsToLoad = [
        'handlebars',
    ];

    private Tests\Functional\Fixtures\Classes\DummyRenderer $renderer;
    private Src\Renderer\Template\Path\ContentObjectPathProvider $pathProvider;
    private Src\Frontend\ContentObject\HandlebarsTemplateContentObject $subject;
    private Frontend\ContentObject\ContentObjectRenderer $contentObjectRenderer;
    private Core\Page\PageRenderer $pageRenderer;

    public function setUp(): void
    {
        parent::setUp();

        $request = $this->buildServerRequest();

        $this->renderer = new Tests\Functional\Fixtures\Classes\DummyRenderer();
        $this->pathProvider = $this->get(Src\Renderer\Template\Path\ContentObjectPathProvider::class);
        $this->subject = new Src\Frontend\ContentObject\HandlebarsTemplateContentObject(
            $this->get(Frontend\ContentObject\ContentDataProcessor::class),
            $this->pathProvider,
            $this->renderer,
            $this->get(Core\TypoScript\TypoScriptService::class),
            $this->get(Src\View\HandlebarsViewFactory::class),
        );
        $this->contentObjectRenderer = new Frontend\ContentObject\ContentObjectRenderer();
        $this->pageRenderer = $this->get(Core\Page\PageRenderer::class);

        $this->subject->setRequest($request);
        $this->subject->setContentObjectRenderer($this->contentObjectRenderer);
        $this->contentObjectRenderer->setRequest($request);
        $this->get(Extbase\Configuration\ConfigurationManagerInterface::class)->setRequest($request);
    }

    #[Framework\Attributes\Test]
    public function renderThrowsExceptionIfTemplateIsNotConfigured(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\ViewIsNotProperlyInitialized(),
        );

        $this->subject->render();
    }

    #[Framework\Attributes\Test]
    public function renderAppliesGivenFormatToView(): void
    {
        $this->subject->render([
            'template' => 'foo',
            'format' => 'hbs',
        ]);

        self::assertSame('hbs', $this->renderer->lastView?->getFormat());
    }

    #[Framework\Attributes\Test]
    public function renderAppliesStdWrapOnFormatConfig(): void
    {
        $this->subject->render([
            'template' => 'foo',
            'format' => 'hbs',
            'format.' => [
                'wrap' => 'html.|',
            ],
        ]);

        self::assertSame('html.hbs', $this->renderer->lastView?->getFormat());
    }

    #[Framework\Attributes\Test]
    public function renderAppliesGivenTemplateNameToView(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\TemplateFileIsInvalid('foo'),
        );

        $this->subject->render([
            'templateName' => 'foo',
        ]);
    }

    #[Framework\Attributes\Test]
    public function renderAppliesStdWrapOnTemplateNameConfig(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\TemplateFileIsInvalid('foo/baz'),
        );

        $this->subject->render([
            'templateName' => 'foo',
            'templateName.' => [
                'wrap' => '|/baz',
            ],
        ]);
    }

    #[Framework\Attributes\Test]
    public function renderAppliesGivenTemplateToView(): void
    {
        self::assertSame(
            'foo',
            $this->subject->render([
                'template' => 'foo',
            ]),
        );
    }

    #[Framework\Attributes\Test]
    public function renderAppliesStdWrapOnTemplateConfig(): void
    {
        self::assertSame(
            'foo baz',
            $this->subject->render([
                'template' => 'foo',
                'template.' => [
                    'noTrimWrap' => '|| baz|',
                ],
            ]),
        );
    }

    #[Framework\Attributes\Test]
    public function renderAppliesGivenFileToView(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\TemplateFileIsInvalid('foo'),
        );

        $this->subject->render([
            'file' => 'foo',
        ]);
    }

    #[Framework\Attributes\Test]
    public function renderAppliesStdWrapOnFileConfig(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\TemplateFileIsInvalid('foo/baz'),
        );

        $this->subject->render([
            'file' => 'foo',
            'file.' => [
                'wrap' => '|/baz',
            ],
        ]);
    }

    #[Framework\Attributes\Test]
    public function renderPushesResolvedTemplatePathsToContentObjectPathProvider(): void
    {
        $this->renderer->testClosure = function () {
            self::assertSame([10 => 'foo'], $this->pathProvider->getTemplateRootPaths());
            self::assertSame([10 => 'baz'], $this->pathProvider->getPartialRootPaths());

            return 'foo';
        };

        $this->subject->render([
            'templateRootPaths.' => [
                '10' => 'foo',
            ],
            'partialRootPaths.' => [
                '10' => 'baz',
            ],
        ]);

        self::assertSame([], $this->pathProvider->getTemplateRootPaths());
        self::assertSame([], $this->pathProvider->getPartialRootPaths());
    }

    #[Framework\Attributes\Test]
    public function renderResolvesAndAppliesVariablesFromConfig(): void
    {
        $expected = [
            'data' => [],
            'current' => null,
            'foo' => 'baz',
        ];

        $this->subject->render([
            'template' => 'foo',
            'variables.' => [
                'foo' => 'TEXT',
                'foo.' => [
                    'value' => 'baz',
                ],
            ],
        ]);

        self::assertEquals($expected, $this->renderer->lastView?->getVariables());
    }

    #[Framework\Attributes\Test]
    public function renderResolvesAndAppliesSimpleVariablesFromConfig(): void
    {
        $expected = [
            'data' => [],
            'current' => null,
            'foo' => [
                'baz' => 'boo',
            ],
        ];

        $this->subject->render([
            'template' => 'foo',
            'variables.' => [
                'foo.' => [
                    'baz' => 'boo',
                ],
            ],
        ]);

        self::assertEquals($expected, $this->renderer->lastView?->getVariables());
    }

    #[Framework\Attributes\Test]
    public function renderResolvesAndAppliesMixedVariablesFromConfig(): void
    {
        $expected = [
            'data' => [],
            'current' => null,
            'foo' => [
                'baz' => 'boo',
            ],
        ];

        $this->subject->render([
            'template' => 'foo',
            'variables.' => [
                'foo.' => [
                    'baz' => 'TEXT',
                    'baz.' => [
                        'value' => 'boo',
                    ],
                ],
            ],
        ]);

        self::assertEquals($expected, $this->renderer->lastView?->getVariables());
    }

    #[Framework\Attributes\Test]
    public function renderResolvesReferencedVariablesFromConfig(): void
    {
        $expected = [
            'data' => [],
            'current' => null,
            'foo' => 'boo',
        ];

        $astBuilder = new Core\TypoScript\AST\AstBuilder(new Core\EventDispatcher\NoopEventDispatcher());
        $factory = $this->get(Core\TypoScript\TypoScriptStringFactory::class);
        $rootNode = $factory->parseFromString('', $astBuilder);

        $frontendTypoScript = new Core\TypoScript\FrontendTypoScript($rootNode, [], [], []);
        $frontendTypoScript->setSetupTree($rootNode);
        $frontendTypoScript->setSetupArray([
            'fooContext' => 'TEXT',
            'fooContext.' => [
                'value' => 'boo',
            ],
        ]);

        $this->contentObjectRenderer->setRequest(
            $this->contentObjectRenderer->getRequest()->withAttribute('frontend.typoscript', $frontendTypoScript),
        );

        $this->subject->render([
            'template' => 'foo',
            'variables.' => [
                'foo' => '< fooContext',
            ],
        ]);

        self::assertEquals($expected, $this->renderer->lastView?->getVariables());
    }

    #[Framework\Attributes\Test]
    public function renderDoesNotApplyEmptyVariablesOnDefinedRemoveIfEmptyConfig(): void
    {
        $expected = [
            'data' => [],
            'current' => null,
            'foo' => [],
        ];

        $this->subject->render([
            'template' => 'foo',
            'variables.' => [
                'foo.' => [
                    'baz' => 'TEXT',
                    'baz.' => [
                        'value' => '',
                        'removeIfEmpty' => '1',
                    ],
                ],
            ],
        ]);

        self::assertEquals($expected, $this->renderer->lastView?->getVariables());
    }

    #[Framework\Attributes\Test]
    public function renderCallsDataProcessorsAndAppliesVariables(): void
    {
        $this->contentObjectRenderer->data = [
            'foo' => 'baz,boo',
        ];

        $expected = [
            'data' => [
                'foo' => 'baz,boo',
            ],
            'current' => null,
            'foo' => [
                [
                    'baz',
                    'boo',
                ],
            ],
        ];

        $this->subject->render([
            'template' => 'foo',
            'dataProcessing.' => [
                '10' => 'comma-separated-value',
                '10.' => [
                    'fieldName' => 'foo',
                    'as' => 'foo',
                ],
            ],
        ]);

        self::assertEquals($expected, $this->renderer->lastView?->getVariables());
    }

    #[Framework\Attributes\Test]
    public function renderResolvesAndAppliesSettingsFromConfig(): void
    {
        $expected = [
            'data' => [],
            'current' => null,
            'settings' => [
                'foo' => [
                    'baz' => 'boo',
                ],
            ],
        ];

        $this->subject->render([
            'template' => 'foo',
            'settings.' => [
                'foo.' => [
                    'baz' => 'boo',
                ],
            ],
        ]);

        self::assertEquals($expected, $this->renderer->lastView?->getVariables());
    }

    #[Framework\Attributes\Test]
    public function renderRendersAndAppliesHeaderAssets(): void
    {
        $this->subject->render([
            'template' => 'foo',
            'headerAssets' => 'HANDLEBARSTEMPLATE',
            'headerAssets.' => [
                'template' => 'foo header assets',
            ],
        ]);

        self::assertStringContainsString('foo header assets', $this->pageRenderer->render());
    }

    #[Framework\Attributes\Test]
    public function renderRendersAndAppliesFooterAssets(): void
    {
        $this->subject->render([
            'template' => 'foo',
            'footerAssets' => 'HANDLEBARSTEMPLATE',
            'footerAssets.' => [
                'template' => 'foo footer assets',
            ],
        ]);

        self::assertStringContainsString('foo footer assets', $this->pageRenderer->render());
    }

    #[Framework\Attributes\Test]
    public function renderAppliesStdWrapToRenderedContent(): void
    {
        self::assertSame(
            'foo baz',
            $this->subject->render([
                'template' => 'foo',
                'stdWrap.' => [
                    'noTrimWrap' => '|| baz|',
                ],
            ]),
        );
    }
}
