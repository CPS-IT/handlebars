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
 * ContentHelperTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Helper\ContentHelper::class)]
final class ContentHelperTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\FrontendRequestTrait;
    use Tests\HandlebarsTemplateResolverTrait;

    protected array $testExtensionsToLoad = [
        'handlebars',
        'test_extension',
    ];

    protected bool $initializeDatabase = false;

    private Src\Renderer\HandlebarsRenderer $renderer;
    private Log\Test\TestLogger $logger;

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

        $helperRegistry->add('extend', new Src\Renderer\Helper\ExtendHelper($this->renderer));
        $helperRegistry->add('content', new Src\Renderer\Helper\ContentHelper($this->logger));
        $helperRegistry->add('block', new Src\Renderer\Helper\BlockHelper());

        $this->buildServerRequest();
    }

    #[Framework\Attributes\Test]
    public function helperCanBeCalledFromExtendedLayout(): void
    {
        $actual = $this->renderer->render(
            new Src\Renderer\RenderingContext(
                '@main-layout-extended',
                [
                    'templateName' => '@main-layout',
                ],
            ),
        );

        $expected = implode(PHP_EOL, [
            'this is the main block:',
            '',
            '[ ]+main block',
            '[ ]+injected',
            '',
            'this is the second block:',
            '',
            '[ ]+injected',
            '[ ]+second block',
            '',
            'this is the third block:',
            '',
            '[ ]+injected',
            '',
            'this is the fourth block:',
            '',
            '[ ]+injected',
            '',
            'this is the end. bye bye',
        ]);

        self::assertMatchesRegularExpression('/^' . $expected . '$/', trim($actual));
    }

    #[Framework\Attributes\Test]
    public function helperCannotBeCalledOutsideOfExtendedLayout(): void
    {
        $this->renderer->render(
            new Src\Renderer\RenderingContext('@main-layout-content-only'),
        );

        self::assertTrue(
            $this->logger->hasError([
                'message' => 'Handlebars layout helper "content" can only be used within an "extend" helper block!',
                'context' => [
                    'name' => 'main',
                ],
            ])
        );
    }

    #[Framework\Attributes\Test]
    public function helperUsesReplaceModeIfInvalidModeIsGiven(): void
    {
        $this->renderer->render(
            new Src\Renderer\RenderingContext(
                '@main-layout-extended-with-invalid-mode',
                [
                    'templateName' => '@main-layout',
                ],
            ),
        );

        self::assertTrue(
            $this->logger->hasWarning([
                'message' => 'Handlebars layout helper "content" has invalid mode "foo". Falling back to "replace".',
                'context' => [
                    'name' => 'main',
                ],
            ])
        );
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('helperCanBeCalledToConditionallyRenderBlocksDataProvider')]
    public function helperCanBeCalledToConditionallyRenderBlocks(bool $renderSecondBlock, string $expected): void
    {
        $actual = $this->renderer->render(
            new Src\Renderer\RenderingContext(
                '@main-layout-extended-with-conditional-contents',
                [
                    'templateName' => '@main-layout-conditional-block',
                    'renderSecondBlock' => $renderSecondBlock,
                ],
            ),
        );

        self::assertMatchesRegularExpression('/^' . $expected . '$/', trim($actual));
    }

    /**
     * @return \Generator<string, array{bool, string}>
     */
    public static function helperCanBeCalledToConditionallyRenderBlocksDataProvider(): \Generator
    {
        yield 'without second block' => [false, ''];
        yield 'with second block' => [true, 'main block\n+[ ]+second block'];
    }
}
