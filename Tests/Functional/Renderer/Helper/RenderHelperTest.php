<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2024 Elias Häußler <e.haeussler@familie-redlich.de>
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

namespace Fr\Typo3Handlebars\Tests\Functional\Renderer\Helper;

use Fr\Typo3Handlebars as Src;
use Fr\Typo3Handlebars\TestExtension;
use Fr\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use Psr\Log;
use Symfony\Component\EventDispatcher;
use TYPO3\CMS\Core;
use TYPO3\CMS\Frontend;
use TYPO3\TestingFramework;

/**
 * RenderHelperTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Helper\RenderHelper::class)]
final class RenderHelperTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\HandlebarsTemplateResolverTrait;

    protected array $testExtensionsToLoad = [
        'test_extension',
    ];

    protected bool $initializeDatabase = false;

    private Src\Renderer\HandlebarsRenderer $renderer;
    private Frontend\ContentObject\ContentObjectRenderer $contentObjectRenderer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->templateRootPath = 'EXT:test_extension/Resources/Templates/';
        $this->templateResolver = new Src\Renderer\Template\FlatTemplateResolver($this->getTemplatePaths());
        $this->renderer = new Src\Renderer\HandlebarsRenderer(
            new Src\Cache\NullCache(),
            new EventDispatcher\EventDispatcher(),
            new Log\NullLogger(),
            $this->templateResolver,
        );
        $this->contentObjectRenderer = new Frontend\ContentObject\ContentObjectRenderer();
        $this->contentObjectRenderer->start([]);
        $this->contentObjectRenderer->setRequest(new Core\Http\ServerRequest());

        $subject = new Src\Renderer\Helper\RenderHelper(
            $this->renderer,
            new Core\TypoScript\TypoScriptService(),
            $this->contentObjectRenderer,
        );

        $this->renderer->registerHelper('render', [$subject, 'evaluate']);
    }

    #[Framework\Attributes\Test]
    public function helperCanBeCalledWithDefaultContext(): void
    {
        $actual = $this->renderer->render('@render-default-context', [
            '@foo' => [
                'renderedContent' => 'Hello world!',
            ],
        ]);

        self::assertSame('Hello world!', trim($actual));
    }

    #[Framework\Attributes\Test]
    public function helperCanBeCalledWithCustomContext(): void
    {
        $actual = $this->renderer->render('@render-custom-context', [
            'renderData' => [
                'renderedContent' => 'Hello world!',
            ],
        ]);

        self::assertSame('Hello world!', trim($actual));
    }

    #[Framework\Attributes\Test]
    public function helperCanBeCalledWithMergedContext(): void
    {
        $actual = $this->renderer->render('@render-merged-context', [
            '@foo' => [
                'renderedContent' => 'Hello world!',
            ],
            'renderData' => [
                'renderedContent' => 'Lorem ipsum',
            ],
        ]);

        self::assertSame('Lorem ipsum', trim($actual));
    }

    #[Framework\Attributes\Test]
    public function helperCanBeCalledToRenderANonCacheableTemplate(): void
    {
        $GLOBALS['TSFE'] = new Frontend\Controller\TypoScriptFrontendController(
            new Core\Context\Context(),
            new Core\Site\Entity\Site('foo', 1, []),
            new Core\Site\Entity\SiteLanguage(1, 'en', new Core\Http\Uri(), []),
            new Core\Routing\PageArguments(1, 'foo', []),
            new Frontend\Authentication\FrontendUserAuthentication(),
        );
        $GLOBALS['TSFE']->cObj = $this->contentObjectRenderer;

        $actual = $GLOBALS['TSFE']->content = $this->renderer->render('@render-uncached', [
            'renderData' => [
                '_processor' => TestExtension\DummyNonCacheableProcessor::class,
                'foo' => 'baz',
            ],
        ]);

        self::assertMatchesRegularExpression('#^<!--INT_SCRIPT.[^-]+-->$#', trim($actual));

        $GLOBALS['TSFE']->INTincScript(new Core\Http\ServerRequest());
        $content = $GLOBALS['TSFE']->content;

        $expected = [
            'templatePath' => '@foo',
            'context' => [
                'foo' => 'baz',
            ],
        ];

        self::assertJson($content);
        self::assertSame($expected, json_decode($content, true));
    }
}
