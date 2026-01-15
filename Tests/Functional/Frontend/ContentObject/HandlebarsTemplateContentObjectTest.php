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
use Psr\Http\Message;
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
    private Core\Page\AssetCollector $assetCollector;
    private Src\Frontend\ContentObject\HandlebarsTemplateContentObject $subject;
    private Frontend\ContentObject\ContentObjectRenderer $contentObjectRenderer;
    private Core\Page\PageRenderer $pageRenderer;
    private Message\ServerRequestInterface $request;

    public function setUp(): void
    {
        parent::setUp();

        $this->renderer = new Tests\Functional\Fixtures\Classes\DummyRenderer();
        $this->pathProvider = $this->get(Src\Renderer\Template\Path\ContentObjectPathProvider::class);
        $this->assetCollector = $this->get(Core\Page\AssetCollector::class);
        $this->subject = new Src\Frontend\ContentObject\HandlebarsTemplateContentObject(
            $this->get(Frontend\ContentObject\ContentDataProcessor::class),
            $this->pathProvider,
            $this->renderer,
            $this->get(Core\TypoScript\TypoScriptService::class),
            new Src\Frontend\Assets\AssetHandler($this->assetCollector),
        );
        $this->contentObjectRenderer = new Frontend\ContentObject\ContentObjectRenderer();
        $this->pageRenderer = $this->get(Core\Page\PageRenderer::class);

        $this->request = $this->buildServerRequest();
        $this->subject->setRequest($this->request);
        $this->subject->setContentObjectRenderer($this->contentObjectRenderer);
        $this->request = $this->request->withAttribute('currentContentObject', $this->contentObjectRenderer);
        $this->contentObjectRenderer->setRequest($this->request);
        $this->get(Extbase\Configuration\ConfigurationManagerInterface::class)->setRequest($this->request);
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
    public function renderAppliesCurrentRequestToContext(): void
    {
        $this->subject->render([
            'template' => 'foo',
        ]);

        self::assertEquals($this->request, $this->renderer->lastContext?->getRequest());
    }

    #[Framework\Attributes\Test]
    public function renderAppliesGivenFormatToContext(): void
    {
        $this->subject->render([
            'template' => 'foo',
            'format' => 'hbs',
        ]);

        self::assertSame('hbs', $this->renderer->lastContext?->getFormat());
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

        self::assertSame('html.hbs', $this->renderer->lastContext?->getFormat());
    }

    #[Framework\Attributes\Test]
    public function renderAppliesGivenTemplateNameToContext(): void
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
    public function renderAppliesGivenTemplateToContext(): void
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
    public function renderAppliesGivenFileToContext(): void
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

        self::assertEquals($expected, $this->renderer->lastContext?->getVariables());
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

        self::assertEquals($expected, $this->renderer->lastContext?->getVariables());
    }

    #[Framework\Attributes\Test]
    public function renderCollectsConfiguredAssets(): void
    {
        $this->subject->render([
            'template' => 'foo',
            'assets.' => [
                'javaScript' => [
                    'my-script' => [
                        'source' => 'EXT:myext/Resources/Public/JavaScript/app.js',
                    ],
                ],
            ],
        ]);

        self::assertArrayHasKey('my-script', $this->assetCollector->getJavaScripts());
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
