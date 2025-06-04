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

namespace Fr\Typo3Handlebars\Tests\Unit\Extbase\View;

use Fr\Typo3Handlebars as Src;
use Fr\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Core;
use TYPO3\CMS\Frontend;
use TYPO3\TestingFramework;

/**
 * ExtbaseHandlebarsViewResolverTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Extbase\View\ExtbaseHandlebarsViewResolver::class)]
final class ExtbaseHandlebarsViewResolverTest extends TestingFramework\Core\Unit\UnitTestCase
{
    /**
     * @var list<list<mixed>|null>
     */
    private array $cObjGetSingleCalls = [null];

    private Frontend\ContentObject\ContentObjectRenderer&Framework\MockObject\MockObject $contentObjectRendererMock;
    private Tests\Unit\Fixtures\Classes\DummyConfigurationManager $configurationManager;
    private Src\Extbase\View\ExtbaseHandlebarsViewResolver $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->contentObjectRendererMock = $this->createMock(Frontend\ContentObject\ContentObjectRenderer::class);

        $container = new DependencyInjection\Container();
        $container->set(Frontend\ContentObject\ContentObjectRenderer::class, $this->contentObjectRendererMock);
        $container->set(Tests\Unit\Fixtures\Classes\DummyView::class, new Tests\Unit\Fixtures\Classes\DummyView());

        $this->configurationManager = new Tests\Unit\Fixtures\Classes\DummyConfigurationManager();
        $this->configurationManager->configuration = [
            'controllerConfiguration' => [
                'Vendor\\Controller\\FooController' => [
                    'alias' => 'Foo',
                ],
            ],
        ];

        $this->subject = new Src\Extbase\View\ExtbaseHandlebarsViewResolver(
            $container,
            $this->configurationManager,
            new Core\TypoScript\TypoScriptService(),
        );
        $this->subject->setDefaultViewClass(Tests\Unit\Fixtures\Classes\DummyView::class);
    }

    #[Framework\Attributes\Test]
    public function resolveReturnsFallbackViewIfControllerIsUnknown(): void
    {
        self::assertInstanceOf(
            Tests\Unit\Fixtures\Classes\DummyView::class,
            $this->subject->resolve('Vendor\\Controller\\UnknownController', 'baz', 'hbs'),
        );
    }

    #[Framework\Attributes\Test]
    public function resolveReturnsHandlebarsViewWithDefaultConfigurationIfNoHandlebarsConfigurationIsAvailable(): void
    {
        $actual = $this->subject->resolve('Vendor\\Controller\\FooController', 'baz', 'hbs');

        self::assertInstanceOf(Src\Extbase\View\ExtbaseHandlebarsView::class, $actual);

        $this->expectContentObjectConfiguration(
            'HANDLEBARSTEMPLATE',
            [
                'templateName' => 'Foo/baz',
                'format' => 'hbs',
            ],
        );

        $actual->render();
    }

    #[Framework\Attributes\Test]
    public function resolveReturnsHandlebarsViewWithProcessedTemplateName(): void
    {
        $this->configurationManager->configuration['handlebars'] = [
            'templateName' => [
                '_typoScriptNodeValue' => 'CASE',
                'key' => [
                    'field' => 'controllerActionName',
                ],
                'baz' => [
                    '_typoScriptNodeValue' => 'TEXT',
                    'value' => '@baz',
                ],
                'default' => '@not-found',
            ],
        ];

        $this->expectContentObjectConfigurationCalls(2);

        $this->expectContentObjectConfiguration(
            'CASE',
            [
                'key.' => [
                    'field' => 'controllerActionName',
                ],
                'baz' => 'TEXT',
                'baz.' => [
                    'value' => '@baz',
                ],
                'default' => '@not-found',
            ],
            '@baz',
        );

        $this->expectContentObjectConfiguration(
            'HANDLEBARSTEMPLATE',
            [
                'templateName' => '@baz',
                'format' => 'hbs',
            ],
        );

        $actual = $this->subject->resolve('Vendor\\Controller\\FooController', 'baz', 'hbs');

        self::assertInstanceOf(Src\Extbase\View\ExtbaseHandlebarsView::class, $actual);

        $actual->render();
    }

    #[Framework\Attributes\Test]
    public function resolveReturnsHandlebarsViewWithDefaultConfigurationIfProcessedTemplateNameIsEmpty(): void
    {
        $this->configurationManager->configuration['handlebars'] = [
            'templateName' => [
                '_typoScriptNodeValue' => 'CASE',
                'key' => [
                    'field' => 'controllerActionName',
                ],
                'boo' => [
                    '_typoScriptNodeValue' => 'TEXT',
                    'value' => '@boo',
                ],
            ],
        ];

        $this->expectContentObjectConfigurationCalls(2);
        $this->expectContentObjectConfiguration(
            'CASE',
            [
                'key.' => [
                    'field' => 'controllerActionName',
                ],
                'boo' => 'TEXT',
                'boo.' => [
                    'value' => '@boo',
                ],
            ],
            '',
        );
        $this->expectContentObjectConfiguration(
            'HANDLEBARSTEMPLATE',
            [
                'templateName' => 'Foo/baz',
                'format' => 'hbs',
            ],
        );

        $actual = $this->subject->resolve('Vendor\\Controller\\FooController', 'baz', 'hbs');

        self::assertInstanceOf(Src\Extbase\View\ExtbaseHandlebarsView::class, $actual);

        $actual->render();
    }

    /**
     * @param array<string, mixed> $contentObjectConfiguration
     */
    private function expectContentObjectConfiguration(
        string $contentObjectName,
        array $contentObjectConfiguration,
        string $return = '',
    ): void {
        $nextKey = null;
        $count = count($this->cObjGetSingleCalls);

        foreach ($this->cObjGetSingleCalls as $key => $call) {
            if ($call === null) {
                $nextKey = $key;
                break;
            }
        }

        if ($nextKey === null) {
            self::fail('No more calls to cObjGetSingle expected.');
        }

        $this->cObjGetSingleCalls[$nextKey] = [$contentObjectName, $contentObjectConfiguration, $return];

        if ($nextKey === $count - 1) {
            $this->contentObjectRendererMock->expects(self::exactly($count))
                ->method('cObjGetSingle')
                /* @phpstan-ignore argument.type */
                ->willReturnMap($this->cObjGetSingleCalls)
            ;
        }
    }

    private function expectContentObjectConfigurationCalls(int $count): void
    {
        $this->cObjGetSingleCalls = array_fill(0, $count, null);
    }
}
