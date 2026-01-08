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

namespace CPSIT\Typo3Handlebars\Tests\Unit\Service;

use CPSIT\Typo3Handlebars as Src;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * AssetServiceTest
 *
 * @author Vladimir Falcon Piva <v.falcon@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Service\AssetService::class)]
final class AssetServiceTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Core\Page\AssetCollector $assetCollector;
    private Src\Service\AssetService $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assetCollector = new Core\Page\AssetCollector();
        $this->subject = new Src\Service\AssetService(
            $this->assetCollector,
        );
    }

    #[Framework\Attributes\Test]
    public function registerAssetsDoesNothingIfNoAssetsConfigured(): void
    {
        $this->subject->registerAssets([]);

        self::assertEmpty($this->assetCollector->getJavaScripts());
        self::assertEmpty($this->assetCollector->getInlineJavaScripts());
        self::assertEmpty($this->assetCollector->getStyleSheets());
        self::assertEmpty($this->assetCollector->getInlineStyleSheets());
    }

    #[Framework\Attributes\Test]
    public function registerAssetsAddsJavaScriptWithMinimalConfiguration(): void
    {
        $config = [
            'javaScript' => [
                'my-script' => [
                    'source' => 'EXT:myext/Resources/Public/JavaScript/app.js',
                ],
            ],
        ];

        $this->subject->registerAssets($config);

        $assets = $this->assetCollector->getJavaScripts();
        self::assertArrayHasKey('my-script', $assets);
        self::assertSame('EXT:myext/Resources/Public/JavaScript/app.js', $assets['my-script']['source']);
        self::assertEmpty($assets['my-script']['attributes']);
        self::assertEmpty($assets['my-script']['options']);
    }

    #[Framework\Attributes\Test]
    public function registerAssetsAddsJavaScriptWithFullConfiguration(): void
    {
        $config = [
            'javaScript' => [
                'my-script' => [
                    'source' => 'EXT:myext/Resources/Public/JavaScript/app.js',
                    'attributes' => [
                        'async' => '1',
                        'defer' => '1',
                        'crossorigin' => 'anonymous',
                    ],
                    'options' => [
                        'priority' => '1',
                        'useNonce' => '1',
                    ],
                ],
            ],
        ];

        $this->subject->registerAssets($config);

        $assets = $this->assetCollector->getJavaScripts();
        self::assertArrayHasKey('my-script', $assets);
        self::assertSame('EXT:myext/Resources/Public/JavaScript/app.js', $assets['my-script']['source']);
        self::assertSame('async', $assets['my-script']['attributes']['async']);
        self::assertSame('defer', $assets['my-script']['attributes']['defer']);
        self::assertSame('anonymous', $assets['my-script']['attributes']['crossorigin']);
        self::assertTrue($assets['my-script']['options']['priority']);
        self::assertTrue($assets['my-script']['options']['useNonce']);
    }

    #[Framework\Attributes\Test]
    public function registerAssetsAddsStyleSheetWithMediaAttribute(): void
    {
        $config = [
            'css' => [
                'my-styles' => [
                    'source' => 'EXT:myext/Resources/Public/Css/styles.css',
                    'attributes' => [
                        'media' => 'screen and (max-width: 768px)',
                    ],
                ],
            ],
        ];

        $this->subject->registerAssets($config);

        $assets = $this->assetCollector->getStyleSheets();
        self::assertArrayHasKey('my-styles', $assets);
        self::assertSame('EXT:myext/Resources/Public/Css/styles.css', $assets['my-styles']['source']);
        self::assertSame('screen and (max-width: 768px)', $assets['my-styles']['attributes']['media']);
    }

    #[Framework\Attributes\Test]
    public function registerAssetsAddsInlineJavaScript(): void
    {
        $config = [
            'inlineJavaScript' => [
                'my-inline' => [
                    'source' => 'console.log("Hello");',
                    'attributes' => [
                        'type' => 'module',
                    ],
                ],
            ],
        ];

        $this->subject->registerAssets($config);

        $assets = $this->assetCollector->getInlineJavaScripts();
        self::assertArrayHasKey('my-inline', $assets);
        self::assertSame('console.log("Hello");', $assets['my-inline']['source']);
        self::assertSame('module', $assets['my-inline']['attributes']['type']);
    }

    #[Framework\Attributes\Test]
    public function registerAssetsAddsInlineStyleSheet(): void
    {
        $config = [
            'inlineCss' => [
                'my-inline-css' => [
                    'source' => 'body { margin: 0; }',
                ],
            ],
        ];

        $this->subject->registerAssets($config);

        $assets = $this->assetCollector->getInlineStyleSheets();
        self::assertArrayHasKey('my-inline-css', $assets);
        self::assertSame('body { margin: 0; }', $assets['my-inline-css']['source']);
    }

    #[Framework\Attributes\Test]
    public function registerAssetsProcessesMultipleAssetsOfDifferentTypes(): void
    {
        $config = [
            'javaScript' => [
                'js1' => ['source' => 'file1.js'],
                'js2' => ['source' => 'file2.js'],
            ],
            'css' => [
                'css1' => ['source' => 'file1.css'],
            ],
            'inlineJavaScript' => [
                'inline1' => ['source' => 'console.log(1);'],
            ],
        ];

        $this->subject->registerAssets($config);

        self::assertCount(2, $this->assetCollector->getJavaScripts());
        self::assertCount(1, $this->assetCollector->getStyleSheets());
        self::assertCount(1, $this->assetCollector->getInlineJavaScripts());
    }

    #[Framework\Attributes\Test]
    public function registerAssetsThrowsExceptionForMissingSource(): void
    {
        $config = [
            'javaScript' => [
                'my-script' => [
                    'attributes' => ['async' => '1'],
                ],
            ],
        ];

        $this->expectException(Src\Exception\InvalidAssetConfigurationException::class);
        $this->expectExceptionMessage('missing required "source"');

        $this->subject->registerAssets($config);
    }

    #[Framework\Attributes\Test]
    public function registerAssetsThrowsExceptionForEmptySource(): void
    {
        $config = [
            'javaScript' => [
                'my-script' => [
                    'source' => '',
                ],
            ],
        ];

        $this->expectException(Src\Exception\InvalidAssetConfigurationException::class);

        $this->subject->registerAssets($config);
    }

    #[Framework\Attributes\Test]
    public function registerAssetsHandlesBooleanAttributesCorrectly(): void
    {
        $config = [
            'javaScript' => [
                'my-script' => [
                    'source' => 'file.js',
                    'attributes' => [
                        'async' => '1',
                        'defer' => '0',  // Should be omitted
                        'nomodule' => '1',
                    ],
                ],
            ],
        ];

        $this->subject->registerAssets($config);

        $assets = $this->assetCollector->getJavaScripts();
        self::assertSame('async', $assets['my-script']['attributes']['async']);
        self::assertSame('nomodule', $assets['my-script']['attributes']['nomodule']);
        self::assertArrayNotHasKey('defer', $assets['my-script']['attributes']);
    }

    #[Framework\Attributes\Test]
    public function registerAssetsHandlesCssBooleanAttributesCorrectly(): void
    {
        $config = [
            'css' => [
                'my-styles' => [
                    'source' => 'file.css',
                    'attributes' => [
                        'disabled' => '1',
                        'media' => 'screen',
                    ],
                ],
            ],
        ];

        $this->subject->registerAssets($config);

        $assets = $this->assetCollector->getStyleSheets();
        self::assertSame('disabled', $assets['my-styles']['attributes']['disabled']);
        self::assertSame('screen', $assets['my-styles']['attributes']['media']);
    }

    #[Framework\Attributes\Test]
    public function registerAssetsHandlesMultipleBooleanAttributesCombinations(): void
    {
        $config = [
            'javaScript' => [
                'my-script' => [
                    'source' => 'file.js',
                    'attributes' => [
                        'async' => '1',
                        'defer' => '1',
                        'nomodule' => '0',  // Should be omitted
                        'type' => 'module',
                        'crossorigin' => 'anonymous',
                    ],
                ],
            ],
        ];

        $this->subject->registerAssets($config);

        $assets = $this->assetCollector->getJavaScripts();
        self::assertSame('async', $assets['my-script']['attributes']['async']);
        self::assertSame('defer', $assets['my-script']['attributes']['defer']);
        self::assertSame('module', $assets['my-script']['attributes']['type']);
        self::assertSame('anonymous', $assets['my-script']['attributes']['crossorigin']);
        self::assertArrayNotHasKey('nomodule', $assets['my-script']['attributes']);
    }

    #[Framework\Attributes\Test]
    public function registerAssetsHandlesOptionsCorrectly(): void
    {
        $config = [
            'javaScript' => [
                'my-script' => [
                    'source' => 'file.js',
                    'options' => [
                        'priority' => '1',
                        'useNonce' => '0',
                    ],
                ],
            ],
        ];

        $this->subject->registerAssets($config);

        $assets = $this->assetCollector->getJavaScripts();
        self::assertTrue($assets['my-script']['options']['priority']);
        self::assertFalse($assets['my-script']['options']['useNonce']);
    }

    #[Framework\Attributes\Test]
    public function registerAssetsThrowsExceptionForInvalidConfiguration(): void
    {
        $config = [
            'javaScript' => [
                'invalid-script' => [
                    // Missing source
                ],
            ],
        ];

        $this->expectException(Src\Exception\InvalidAssetConfigurationException::class);
        $this->expectExceptionMessage('missing required "source"');

        $this->subject->registerAssets($config);
    }

    #[Framework\Attributes\Test]
    public function registerAssetsHandlesAllFourAssetTypes(): void
    {
        $config = [
            'javaScript' => [
                'external-js' => ['source' => 'external.js'],
            ],
            'inlineJavaScript' => [
                'inline-js' => ['source' => 'console.log("inline");'],
            ],
            'css' => [
                'external-css' => ['source' => 'external.css'],
            ],
            'inlineCss' => [
                'inline-css' => ['source' => 'body { color: red; }'],
            ],
        ];

        $this->subject->registerAssets($config);

        self::assertCount(1, $this->assetCollector->getJavaScripts());
        self::assertCount(1, $this->assetCollector->getInlineJavaScripts());
        self::assertCount(1, $this->assetCollector->getStyleSheets());
        self::assertCount(1, $this->assetCollector->getInlineStyleSheets());
    }

    #[Framework\Attributes\Test]
    public function registerAssetsThrowsExceptionForUnknownAssetType(): void
    {
        $config = [
            'unknownType' => [
                'my-asset' => ['source' => 'file.js'],
            ],
        ];

        $this->expectException(Src\Exception\InvalidAssetConfigurationException::class);
        $this->expectExceptionMessage('Unknown asset type "unknownType"');

        $this->subject->registerAssets($config);
    }
}
