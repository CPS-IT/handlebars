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

namespace Fr\Typo3Handlebars\Tests\Functional\Renderer\Helper;

use Fr\Typo3Handlebars as Src;
use Fr\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use Psr\Log;
use Symfony\Component\EventDispatcher;
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
            new Log\NullLogger(),
            $this->templateResolver,
            new Src\Renderer\Variables\VariableBag([]),
        );

        $subject = new Src\Renderer\Helper\RenderHelper($this->renderer);

        $helperRegistry->add('render', $subject);

        $this->buildServerRequest();
    }

    #[Framework\Attributes\Test]
    public function helperCanBeCalledWithDefaultContext(): void
    {
        $actual = $this->renderer->render(
            new Src\Renderer\Template\View\HandlebarsView(
                '@render-default-context',
                [
                    '@foo' => [
                        'renderedContent' => 'Hello world!',
                    ],
                ],
            ),
        );

        self::assertSame('Hello world!', trim($actual));
    }

    #[Framework\Attributes\Test]
    public function helperCanBeCalledWithCustomContext(): void
    {
        $actual = $this->renderer->render(
            new Src\Renderer\Template\View\HandlebarsView(
                '@render-custom-context',
                [
                    'renderData' => [
                        'renderedContent' => 'Hello world!',
                    ],
                ],
            ),
        );

        self::assertSame('Hello world!', trim($actual));
    }

    #[Framework\Attributes\Test]
    public function helperCanBeCalledWithMergedContext(): void
    {
        $actual = $this->renderer->render(
            new Src\Renderer\Template\View\HandlebarsView(
                '@render-merged-context',
                [
                    '@foo' => [
                        'renderedContent' => 'Hello world!',
                    ],
                    'renderData' => [
                        'renderedContent' => 'Lorem ipsum',
                    ],
                ],
            ),
        );

        self::assertSame('Lorem ipsum', trim($actual));
    }
}
