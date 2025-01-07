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
    use Tests\HandlebarsTemplateResolverTrait;

    protected array $testExtensionsToLoad = [
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

        $helperRegistry->add('extend', new Src\Renderer\Helper\ExtendHelper($this->renderer));
        $helperRegistry->add('jsonEncode', new TestExtension\JsonHelper());
    }

    #[Framework\Attributes\Test]
    public function helperCanBeCalledWithoutCustomContext(): void
    {
        $actual = trim($this->renderer->render('@simple-layout-extended'));
        $expected = [];

        self::assertJson($actual);

        $json = json_decode($actual, true);
        unset($json['_layoutStack']);

        self::assertSame($expected, $json);
    }

    #[Framework\Attributes\Test]
    public function helperCanBeCalledWithCustomContext(): void
    {
        $actual = trim($this->renderer->render('@simple-layout-extended-with-context', [
            'customContext' => [
                'foo' => 'baz',
            ],
        ]));
        $expected = [
            'customContext' => [
                'foo' => 'baz',
            ],
            'foo' => 'baz',
        ];

        self::assertJson($actual);

        $json = json_decode($actual, true);
        unset($json['_layoutStack']);

        self::assertSame($expected, $json);
    }

    #[Framework\Attributes\Test]
    public function helperReplacesVariablesCorrectlyInAllContexts(): void
    {
        $actual = trim($this->renderer->render('@simple-layout-extended-with-context', [
            'foo' => 123,
            'customContext' => [
                'foo' => 456,
            ],
        ]));

        $expected = [
            'foo' => 456,
            'customContext' => [
                'foo' => 456,
            ],
        ];

        self::assertJson($actual);

        $json = json_decode($actual, true);
        unset($json['_layoutStack']);

        self::assertSame($expected, $json);
    }
}
