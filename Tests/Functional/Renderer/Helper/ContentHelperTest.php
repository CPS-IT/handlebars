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
use Fr\Typo3Handlebars\Tests;
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
    use Tests\HandlebarsTemplateResolverTrait;

    protected bool $initializeDatabase = false;

    protected array $testExtensionsToLoad = [
        'test_extension',
    ];

    private Src\Renderer\HandlebarsRenderer $renderer;
    private Log\Test\TestLogger $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $helperRegistry = new Src\Renderer\Helper\HelperRegistry(new Log\NullLogger());

        $this->templateRootPath = 'EXT:test_extension/Resources/Templates/';
        $this->logger = new Log\Test\TestLogger();
        $this->templateResolver = new Src\Renderer\Template\FlatTemplateResolver($this->getTemplatePaths());
        $this->renderer = new Src\Renderer\HandlebarsRenderer(
            new Src\Cache\NullCache(),
            new EventDispatcher\EventDispatcher(),
            $helperRegistry,
            $this->logger,
            $this->templateResolver,
        );

        $helperRegistry->add('extend', new Src\Renderer\Helper\ExtendHelper($this->renderer));
        $helperRegistry->add('content', new Src\Renderer\Helper\ContentHelper($this->logger));
        $helperRegistry->add('block', new Src\Renderer\Helper\BlockHelper());
    }

    #[Framework\Attributes\Test]
    public function helperCanBeCalledFromExtendedLayout(): void
    {
        $actual = trim($this->renderer->render('@main-layout-extended', [
            'templateName' => '@main-layout',
        ]));
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

        self::assertMatchesRegularExpression('/^' . $expected . '$/', $actual);
    }

    #[Framework\Attributes\Test]
    public function helperCannotBeCalledOutsideOfExtendedLayout(): void
    {
        $this->renderer->render('@main-layout-content-only');

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
        $this->renderer->render('@main-layout-extended-with-invalid-mode', [
            'templateName' => '@main-layout',
        ]);

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
        $actual = trim($this->renderer->render('@main-layout-extended-with-conditional-contents', [
            'templateName' => '@main-layout-conditional-block',
            'renderSecondBlock' => $renderSecondBlock,
        ]));

        self::assertMatchesRegularExpression('/^' . $expected . '$/', $actual);
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
