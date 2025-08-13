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
use CPSIT\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use Psr\Log;
use Symfony\Component\EventDispatcher;
use TYPO3\TestingFramework;

/**
 * BlockHelperTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Helper\BlockHelper::class)]
final class BlockHelperTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\FrontendRequestTrait;
    use Tests\HandlebarsTemplateResolverTrait;

    protected bool $initializeDatabase = false;

    protected array $testExtensionsToLoad = [
        'handlebars',
        'test_extension',
    ];

    private Src\Renderer\HandlebarsRenderer $renderer;

    protected function setUp(): void
    {
        $this->allowAdditionalRootPaths();

        parent::setUp();

        $helperRegistry = new Src\Renderer\Helper\HelperRegistry(new Log\NullLogger());
        $layoutStack = new Src\Renderer\Component\Layout\HandlebarsLayoutStack();

        $this->templateRootPath = 'EXT:test_extension/Resources/Templates/';
        $this->templateResolver = new Src\Renderer\Template\FlatTemplateResolver($this->getTemplatePaths());
        $this->renderer = new Src\Renderer\HandlebarsRenderer(
            new Src\Cache\NullCache(),
            new EventDispatcher\EventDispatcher(),
            $helperRegistry,
            $this->templateResolver,
            new Src\Renderer\Variables\VariableBag([]),
        );

        $helperRegistry->add('extend', new Src\Renderer\Helper\ExtendHelper($layoutStack, $this->renderer));
        $helperRegistry->add('content', new Src\Renderer\Helper\ContentHelper($layoutStack, new Log\NullLogger()));
        $helperRegistry->add('block', new Src\Renderer\Helper\BlockHelper($layoutStack));

        $this->buildServerRequest();
    }

    #[Framework\Attributes\Test]
    public function helperCanBeCalledFromMainLayout(): void
    {
        $actual = $this->renderer->render(
            new Src\Renderer\RenderingContext('@main-layout'),
        );

        $expected = implode(PHP_EOL, [
            'this is the main block:',
            '',
            '[ ]+main block',
            '',
            'this is the second block:',
            '',
            '[ ]+second block',
            '',
            'this is the third block:',
            '',
            '[ ]+third block',
            '',
            'this is the fourth block:',
            '',
            '[ ]+fourth block',
            '',
            'this is the end. bye bye',
        ]);

        self::assertMatchesRegularExpression('/^' . $expected . '$/', trim($actual));
    }

    #[Framework\Attributes\Test]
    public function helperCanBeCalledFromExtendedLayout(): void
    {
        $actual = $this->renderer->render(
            new Src\Renderer\RenderingContext(
                '@main-layout-extended-with-fifth-content',
                [
                    'templateName' => '@main-layout',
                ],
            ),
        );

        $expected = implode(PHP_EOL, [
            'this is the main block:',
            '',
            '[ ]+main block',
            '',
            'this is the second block:',
            '',
            '[ ]+second block',
            '',
            'this is the third block:',
            '',
            '[ ]+third block',
            '',
            'this is the fourth block:',
            '',
            '[ ]+fourth block',
            '',
            '[ ]+this is the fifth block:',
            '',
            '[ ]+fifth block',
            '[ ]+injected',
            '',
            'this is the end. bye bye',
        ]);

        self::assertMatchesRegularExpression('/^' . $expected . '$/', trim($actual));
    }
}
