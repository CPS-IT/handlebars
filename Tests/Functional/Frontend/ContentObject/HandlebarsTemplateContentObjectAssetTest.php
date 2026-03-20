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
use TYPO3\CMS\Frontend;
use TYPO3\TestingFramework;

/**
 * HandlebarsTemplateContentObjectAssetTest
 *
 * @author Vladimir Falcon Piva <v.falcon@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Frontend\ContentObject\HandlebarsTemplateContentObject::class)]
#[Framework\Attributes\CoversClass(Src\Frontend\Assets\AssetHandler::class)]
final class HandlebarsTemplateContentObjectAssetTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\FrontendRequestTrait;

    protected array $testExtensionsToLoad = [
        'handlebars',
    ];

    private Core\Page\AssetCollector $assetCollector;
    private Src\Frontend\ContentObject\HandlebarsTemplateContentObject $subject;
    private Frontend\ContentObject\ContentObjectRenderer $contentObjectRenderer;

    public function setUp(): void
    {
        parent::setUp();

        $request = $this->buildServerRequest();

        $this->assetCollector = $this->get(Core\Page\AssetCollector::class);
        $this->subject = new Src\Frontend\ContentObject\HandlebarsTemplateContentObject(
            $this->get(Frontend\ContentObject\ContentDataProcessor::class),
            $this->get(Src\Renderer\Template\Path\ContentObjectPathProvider::class),
            $this->get(Src\Renderer\Renderer::class),
            $this->get(Core\TypoScript\TypoScriptService::class),
            $this->get(Src\Frontend\Assets\AssetHandler::class),
        );

        $this->contentObjectRenderer = new Frontend\ContentObject\ContentObjectRenderer();
        $this->contentObjectRenderer->setRequest($request);

        $this->subject->setRequest($request);
        $this->subject->setContentObjectRenderer($this->contentObjectRenderer);
    }

    #[Framework\Attributes\Test]
    public function renderRegistersJavaScriptAsset(): void
    {
        $this->subject->render([
            'template' => 'foo',
            'assets.' => [
                'javaScript.' => [
                    'test-script.' => [
                        'source' => 'EXT:handlebars/Resources/Public/JavaScript/test.js',
                    ],
                ],
            ],
        ]);

        self::assertTrue($this->assetCollector->hasJavaScript('test-script'));
        $assets = $this->assetCollector->getJavaScripts();
        self::assertIsArray($assets['test-script']);
        self::assertSame(
            'EXT:handlebars/Resources/Public/JavaScript/test.js',
            $assets['test-script']['source'],
        );
    }

    #[Framework\Attributes\Test]
    public function renderRegistersJavaScriptWithAttributes(): void
    {
        $this->subject->render([
            'template' => 'foo',
            'assets.' => [
                'javaScript.' => [
                    'test-script.' => [
                        'source' => 'test.js',
                        'attributes.' => [
                            'async' => '1',
                            'crossorigin' => 'anonymous',
                        ],
                    ],
                ],
            ],
        ]);

        $assets = $this->assetCollector->getJavaScripts();
        self::assertIsArray($assets['test-script']);
        self::assertIsArray($assets['test-script']['attributes']);
        self::assertSame('async', $assets['test-script']['attributes']['async']);
        self::assertSame('anonymous', $assets['test-script']['attributes']['crossorigin']);
    }

    #[Framework\Attributes\Test]
    public function renderRegistersStyleSheetAsset(): void
    {
        $this->subject->render([
            'template' => 'foo',
            'assets.' => [
                'css.' => [
                    'test-styles.' => [
                        'source' => 'EXT:handlebars/Resources/Public/Css/test.css',
                        'attributes.' => [
                            'media' => 'print',
                        ],
                    ],
                ],
            ],
        ]);

        self::assertTrue($this->assetCollector->hasStyleSheet('test-styles'));
        $assets = $this->assetCollector->getStyleSheets();
        self::assertIsArray($assets['test-styles']);
        self::assertIsArray($assets['test-styles']['attributes']);
        self::assertSame('print', $assets['test-styles']['attributes']['media']);
    }

    #[Framework\Attributes\Test]
    public function renderRegistersInlineJavaScript(): void
    {
        $jsCode = 'console.log("test");';

        $this->subject->render([
            'template' => 'foo',
            'assets.' => [
                'inlineJavaScript.' => [
                    'test-inline.' => [
                        'source' => $jsCode,
                    ],
                ],
            ],
        ]);

        self::assertTrue($this->assetCollector->hasInlineJavaScript('test-inline'));
        $assets = $this->assetCollector->getInlineJavaScripts();
        self::assertIsArray($assets['test-inline']);
        self::assertSame($jsCode, $assets['test-inline']['source']);
    }

    #[Framework\Attributes\Test]
    public function renderRegistersInlineStyleSheet(): void
    {
        $cssCode = 'body { margin: 0; }';

        $this->subject->render([
            'template' => 'foo',
            'assets.' => [
                'inlineCss.' => [
                    'test-inline-css.' => [
                        'source' => $cssCode,
                    ],
                ],
            ],
        ]);

        self::assertTrue($this->assetCollector->hasInlineStyleSheet('test-inline-css'));
        $assets = $this->assetCollector->getInlineStyleSheets();
        self::assertIsArray($assets['test-inline-css']);
        self::assertSame($cssCode, $assets['test-inline-css']['source']);
    }

    #[Framework\Attributes\Test]
    public function renderRegistersMultipleAssetsOfDifferentTypes(): void
    {
        $this->subject->render([
            'template' => 'foo',
            'assets.' => [
                'javaScript.' => [
                    'js1.' => ['source' => 'file1.js'],
                    'js2.' => ['source' => 'file2.js'],
                ],
                'css.' => [
                    'css1.' => ['source' => 'file1.css'],
                ],
                'inlineJavaScript.' => [
                    'inline1.' => ['source' => 'console.log(1);'],
                ],
            ],
        ]);

        self::assertTrue($this->assetCollector->hasJavaScript('js1'));
        self::assertTrue($this->assetCollector->hasJavaScript('js2'));
        self::assertTrue($this->assetCollector->hasStyleSheet('css1'));
        self::assertTrue($this->assetCollector->hasInlineJavaScript('inline1'));
    }

    #[Framework\Attributes\Test]
    public function renderThrowsExceptionForMissingAssetSource(): void
    {
        $this->expectException(Src\Exception\InvalidAssetConfigurationException::class);
        $this->expectExceptionMessage('missing required "source"');

        $this->subject->render([
            'template' => 'foo',
            'assets.' => [
                'javaScript.' => [
                    'test-script.' => [
                        'attributes.' => ['async' => '1'],
                    ],
                ],
            ],
        ]);
    }

    #[Framework\Attributes\Test]
    public function renderRegistersAssetsWithPriorityOption(): void
    {
        $this->subject->render([
            'template' => 'foo',
            'assets.' => [
                'javaScript.' => [
                    'priority-script.' => [
                        'source' => 'priority.js',
                        'options.' => [
                            'priority' => '1',
                        ],
                    ],
                ],
            ],
        ]);

        $assets = $this->assetCollector->getJavaScripts(true);
        self::assertArrayHasKey('priority-script', $assets);
    }

    #[Framework\Attributes\Test]
    public function renderRegistersAssetsWithUseNonceOption(): void
    {
        $this->subject->render([
            'template' => 'foo',
            'assets.' => [
                'inlineJavaScript.' => [
                    'nonce-script.' => [
                        'source' => 'console.log("nonce");',
                        'options.' => [
                            'useNonce' => '1',
                        ],
                    ],
                ],
            ],
        ]);

        $assets = $this->assetCollector->getInlineJavaScripts();
        self::assertIsArray($assets['nonce-script']);
        self::assertIsArray($assets['nonce-script']['options']);
        self::assertTrue($assets['nonce-script']['options']['useNonce']);
    }

    #[Framework\Attributes\Test]
    public function renderHandlesBooleanAttributesForJavaScript(): void
    {
        $this->subject->render([
            'template' => 'foo',
            'assets.' => [
                'javaScript.' => [
                    'async-script.' => [
                        'source' => 'async.js',
                        'attributes.' => [
                            'async' => '1',
                            'defer' => '1',
                            'nomodule' => '0',
                        ],
                    ],
                ],
            ],
        ]);

        $assets = $this->assetCollector->getJavaScripts();
        self::assertIsArray($assets['async-script']);
        self::assertIsArray($assets['async-script']['attributes']);
        self::assertSame('async', $assets['async-script']['attributes']['async']);
        self::assertSame('defer', $assets['async-script']['attributes']['defer']);
        self::assertArrayNotHasKey('nomodule', $assets['async-script']['attributes']);
    }

    #[Framework\Attributes\Test]
    public function renderHandlesBooleanAttributesForCss(): void
    {
        $this->subject->render([
            'template' => 'foo',
            'assets.' => [
                'css.' => [
                    'disabled-styles.' => [
                        'source' => 'disabled.css',
                        'attributes.' => [
                            'disabled' => '1',
                        ],
                    ],
                ],
            ],
        ]);

        $assets = $this->assetCollector->getStyleSheets();
        self::assertIsArray($assets['disabled-styles']);
        self::assertIsArray($assets['disabled-styles']['attributes']);
        self::assertSame('disabled', $assets['disabled-styles']['attributes']['disabled']);
    }

    #[Framework\Attributes\Test]
    public function renderRegistersAllFourAssetTypes(): void
    {
        $this->subject->render([
            'template' => 'foo',
            'assets.' => [
                'javaScript.' => [
                    'external-js.' => ['source' => 'external.js'],
                ],
                'inlineJavaScript.' => [
                    'inline-js.' => ['source' => 'console.log("inline");'],
                ],
                'css.' => [
                    'external-css.' => ['source' => 'external.css'],
                ],
                'inlineCss.' => [
                    'inline-css.' => ['source' => 'body { color: red; }'],
                ],
            ],
        ]);

        self::assertTrue($this->assetCollector->hasJavaScript('external-js'));
        self::assertTrue($this->assetCollector->hasInlineJavaScript('inline-js'));
        self::assertTrue($this->assetCollector->hasStyleSheet('external-css'));
        self::assertTrue($this->assetCollector->hasInlineStyleSheet('inline-css'));
    }

    #[Framework\Attributes\Test]
    public function renderMaintainsBackwardCompatibilityWithLegacyHeaderAssets(): void
    {
        $pageRenderer = $this->get(Core\Page\PageRenderer::class);

        $this->subject->render([
            'template' => 'foo',
            'headerAssets' => 'TEXT',
            'headerAssets.' => [
                'value' => '<script>legacy();</script>',
            ],
        ]);

        self::assertStringContainsString('legacy()', $pageRenderer->render());
    }

    #[Framework\Attributes\Test]
    public function renderProcessesBothModernAndLegacyAssets(): void
    {
        $pageRenderer = $this->get(Core\Page\PageRenderer::class);

        $this->subject->render([
            'template' => 'foo',
            'assets.' => [
                'javaScript.' => [
                    'modern-script.' => ['source' => 'modern.js'],
                ],
            ],
            'headerAssets' => 'TEXT',
            'headerAssets.' => [
                'value' => '<script>legacy();</script>',
            ],
        ]);

        // Modern asset via AssetCollector
        self::assertTrue($this->assetCollector->hasJavaScript('modern-script'));

        // Legacy asset via PageRenderer
        self::assertStringContainsString('legacy()', $pageRenderer->render());
    }

    #[Framework\Attributes\Test]
    public function renderHandlesCrossoriginAndIntegrityAttributes(): void
    {
        $this->subject->render([
            'template' => 'foo',
            'assets.' => [
                'javaScript.' => [
                    'secure-script.' => [
                        'source' => 'https://cdn.example.com/script.js',
                        'attributes.' => [
                            'crossorigin' => 'anonymous',
                            'integrity' => 'sha384-abc123',
                        ],
                    ],
                ],
            ],
        ]);

        $assets = $this->assetCollector->getJavaScripts();
        self::assertIsArray($assets['secure-script']);
        self::assertIsArray($assets['secure-script']['attributes']);
        self::assertSame('anonymous', $assets['secure-script']['attributes']['crossorigin']);
        self::assertSame('sha384-abc123', $assets['secure-script']['attributes']['integrity']);
    }

    #[Framework\Attributes\Test]
    public function renderHandlesMediaQueryForStyleSheets(): void
    {
        $this->subject->render([
            'template' => 'foo',
            'assets.' => [
                'css.' => [
                    'responsive-styles.' => [
                        'source' => 'responsive.css',
                        'attributes.' => [
                            'media' => 'screen and (min-width: 768px)',
                        ],
                    ],
                ],
            ],
        ]);

        $assets = $this->assetCollector->getStyleSheets();
        self::assertIsArray($assets['responsive-styles']);
        self::assertIsArray($assets['responsive-styles']['attributes']);
        self::assertSame('screen and (min-width: 768px)', $assets['responsive-styles']['attributes']['media']);
    }
}
