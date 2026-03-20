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

namespace CPSIT\Typo3Handlebars\Tests\Functional\Renderer\Helper;

use CPSIT\Typo3Handlebars as Src;
use CPSIT\Typo3Handlebars\TestExtension;
use CPSIT\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use Psr\Log;
use Symfony\Component\EventDispatcher;
use TYPO3\TestingFramework;

/**
 * ExtendHelperTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Helper\ExtendHelper::class)]
final class ExtendHelperTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\FrontendRequestTrait;
    use Tests\HandlebarsTemplateResolverTrait;

    protected array $testExtensionsToLoad = [
        'handlebars',
        'test_extension',
    ];

    protected bool $initializeDatabase = false;

    private Src\Renderer\HandlebarsRenderer $renderer;

    protected function setUp(): void
    {
        $this->allowAdditionalRootPaths();

        parent::setUp();

        $helperRegistry = new Src\Renderer\Helper\HelperRegistry(new Log\NullLogger());

        $this->templateRootPath = 'EXT:test_extension/Resources/Templates/';
        $this->templateResolver = new Src\Renderer\Template\FlatTemplateResolver($this->getTemplatePaths());
        $this->renderer = new Src\Renderer\HandlebarsRenderer(
            new Src\Cache\NullCache(),
            new EventDispatcher\EventDispatcher(),
            $helperRegistry,
            $this->templateResolver,
            new Src\Renderer\Variables\VariableBag([]),
        );

        $helperRegistry->add('extend', new Src\Renderer\Helper\ExtendHelper($this->renderer));
        $helperRegistry->add('jsonEncode', new TestExtension\Renderer\Helper\JsonHelper());

        $this->buildServerRequest();
    }

    #[Framework\Attributes\Test]
    public function helperCanBeCalledWithoutCustomContext(): void
    {
        $actual = trim(
            $this->renderer->render(
                new Src\Renderer\RenderingContext('@simple-layout-extended'),
            ),
        );

        self::assertJson($actual);

        $json = json_decode($actual, true, 512, JSON_THROW_ON_ERROR);

        self::assertIsArray($json);

        unset($json['_layoutActions']);

        self::assertSame([], $json);
    }

    #[Framework\Attributes\Test]
    public function helperCanBeCalledWithCustomContext(): void
    {
        $actual = trim(
            $this->renderer->render(
                new Src\Renderer\RenderingContext(
                    '@simple-layout-extended-with-context',
                    [
                        'customContext' => [
                            'foo' => 'baz',
                        ],
                    ],
                ),
            ),
        );

        $expected = [
            'customContext' => [
                'foo' => 'baz',
            ],
            'foo' => 'baz',
        ];

        self::assertJson($actual);

        $json = json_decode($actual, true, 512, JSON_THROW_ON_ERROR);

        self::assertIsArray($json);

        unset($json['_layoutActions']);

        self::assertSame($expected, $json);
    }

    #[Framework\Attributes\Test]
    public function helperReplacesVariablesCorrectlyInAllContexts(): void
    {
        $actual = trim(
            $this->renderer->render(
                new Src\Renderer\RenderingContext(
                    '@simple-layout-extended-with-context',
                    [
                        'foo' => 123,
                        'customContext' => [
                            'foo' => 456,
                        ],
                    ],
                ),
            ),
        );

        $expected = [
            'foo' => 456,
            'customContext' => [
                'foo' => 456,
            ],
        ];

        self::assertJson($actual);

        $json = json_decode($actual, true, 512, JSON_THROW_ON_ERROR);

        self::assertIsArray($json);

        unset($json['_layoutActions']);

        self::assertSame($expected, $json);
    }
}
