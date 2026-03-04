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

namespace CPSIT\Typo3Handlebars\Tests\Functional\View;

use CPSIT\Typo3Handlebars as Src;
use CPSIT\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Fluid;
use TYPO3\CMS\Frontend;
use TYPO3\TestingFramework;

/**
 * HandlebarsViewFactoryTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\View\HandlebarsViewFactory::class)]
final class HandlebarsViewFactoryTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\FrontendRequestTrait;

    protected bool $initializeDatabase = false;

    protected array $testExtensionsToLoad = [
        'handlebars',
    ];

    /**
     * @var list<list<mixed>|null>
     */
    private array $cObjGetSingleCalls = [null];

    private Frontend\ContentObject\ContentObjectRenderer&Framework\MockObject\MockObject $contentObjectRendererMock;
    private Tests\Unit\Fixtures\Classes\DummyConfigurationManager $configurationManager;
    private Src\View\HandlebarsViewFactory $subject;
    private Extbase\Mvc\Request $request;
    private Extbase\Mvc\ExtbaseRequestParameters $extbaseRequestParameters;

    public function setUp(): void
    {
        parent::setUp();

        $this->contentObjectRendererMock = $this->createMock(Frontend\ContentObject\ContentObjectRenderer::class);

        $this->configurationManager = new Tests\Unit\Fixtures\Classes\DummyConfigurationManager();
        $this->configurationManager->configuration = [
            'controllerConfiguration' => [
                'Vendor\\Extension\\Controller\\FooController' => [
                    'alias' => 'Foo',
                ],
            ],
        ];

        $this->subject = new Src\View\HandlebarsViewFactory(
            $this->configurationManager,
            $this->get(Fluid\View\FluidViewFactory::class),
            $this->get(Core\TypoScript\TypoScriptService::class),
        );

        $this->request = $this->buildExtbaseRequest($extbaseRequestParameters);
        $this->request = $this->request->withAttribute('currentContentObject', $this->contentObjectRendererMock);
        $this->extbaseRequestParameters = $extbaseRequestParameters;
    }

    #[Framework\Attributes\Test]
    public function createReturnsFallbackViewIfNoRequestIsAvailable(): void
    {
        $data = new Core\View\ViewFactoryData();

        self::assertInstanceOf(
            Fluid\View\FluidViewAdapter::class,
            $this->subject->create($data),
        );
    }

    #[Framework\Attributes\Test]
    public function createReturnsFallbackViewIfNoCurrentContentObjectIsAvailable(): void
    {
        $this->request = $this->request->withoutAttribute('currentContentObject');

        $data = new Core\View\ViewFactoryData(request: $this->request);

        self::assertInstanceOf(
            Fluid\View\FluidViewAdapter::class,
            $this->subject->create($data),
        );
    }

    #[Framework\Attributes\Test]
    public function createReturnsFallbackViewIfControllerIsUnknown(): void
    {
        $this->extbaseRequestParameters->setControllerObjectName('Vendor\\Extension\\Controller\\UnknownController');
        $this->request = $this->buildExtbaseRequest($this->extbaseRequestParameters);

        $data = new Core\View\ViewFactoryData(request: $this->request, format: 'hbs');

        self::assertInstanceOf(
            Fluid\View\FluidViewAdapter::class,
            $this->subject->create($data),
        );
    }

    #[Framework\Attributes\Test]
    public function createReturnsFallbackViewWithDefaultConfigurationIfNoHandlebarsConfigurationIsAvailable(): void
    {
        $data = new Core\View\ViewFactoryData(request: $this->request, format: 'hbs');

        self::assertInstanceOf(
            Fluid\View\FluidViewAdapter::class,
            $this->subject->create($data),
        );
    }

    #[Framework\Attributes\Test]
    public function createReturnsHandlebarsViewWithDefaultConfigurationIfNoHandlebarsConfigurationIsAvailable(): void
    {
        $extbaseRequestParameters = new Extbase\Mvc\ExtbaseRequestParameters(Src\TestExtension\Controller\TestController::class);
        $extbaseRequestParameters->setControllerActionName('foo');

        $this->request = $this->buildExtbaseRequest($extbaseRequestParameters);
        $this->request = $this->request->withAttribute('currentContentObject', $this->contentObjectRendererMock);

        $this->configurationManager->configuration = [
            'controllerConfiguration' => [
                Src\TestExtension\Controller\TestController::class => [
                    'alias' => 'Test',
                ],
            ],
        ];

        $data = new Core\View\ViewFactoryData(request: $this->request, format: 'hbs');

        $actual = $this->subject->create($data);

        self::assertInstanceOf(Src\View\HandlebarsView::class, $actual);
        self::assertSame($this->request, $actual->getRequest());

        $this->expectContentObjectConfiguration(
            'HANDLEBARSTEMPLATE',
            [
                'templateName' => 'Test/foo',
                'format' => 'hbs',
            ],
        );

        $actual->render();
    }

    /**
     * @return \Generator<string, array{array<string, mixed>}>
     */
    public static function createReturnsHandlebarsViewWithResolvedConfigurationDataProvider(): \Generator
    {
        $barConfiguration = [
            'templateName' => '@bar',
            'variables' => [
                'foo' => 'bar',
            ],
        ];
        $bazConfiguration = [
            'templateName' => '@baz',
            'variables' => [
                'foo' => 'baz',
            ],
        ];

        yield 'controller FQCN' => [
            [
                'Vendor\\Extension\\Controller\\BarController' => $barConfiguration,
                'Vendor\\Extension\\Controller\\FooController' => $bazConfiguration,
            ],
        ];
        yield 'controller name and action name' => [
            [
                'Foo::bar' => $barConfiguration,
                'Foo::baz' => $bazConfiguration,
            ],
        ];
        yield 'controller name' => [
            [
                'Bar' => $barConfiguration,
                'Foo' => $bazConfiguration,
            ],
        ];
        yield 'fallback' => [
            [
                'Foo::bar' => $barConfiguration,
                'default' => $bazConfiguration,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $configuration
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('createReturnsHandlebarsViewWithResolvedConfigurationDataProvider')]
    public function createReturnsHandlebarsViewWithResolvedConfiguration(array $configuration): void
    {
        $this->configurationManager->configuration['handlebars'] = $configuration;

        $this->expectContentObjectConfigurationCalls(1);
        $this->expectContentObjectConfiguration(
            'HANDLEBARSTEMPLATE',
            [
                'templateName' => '@baz',
                'variables.' => [
                    'foo' => 'baz',
                ],
                'format' => 'hbs',
            ],
        );

        $data = new Core\View\ViewFactoryData(request: $this->request, format: 'hbs');

        $actual = $this->subject->create($data);

        self::assertInstanceOf(Src\View\HandlebarsView::class, $actual);

        $actual->render();
    }

    #[Framework\Attributes\Test]
    public function createReturnsHandlebarsViewWithMergedConfigurations(): void
    {
        $this->configurationManager->configuration['handlebars'] = [
            'default' => [
                'templateName' => '@default',
                'variables' => [
                    'default' => 'foo',
                ],
            ],
            'Foo' => [
                'templateName' => '@foo',
                'variables' => [
                    'foo' => 'foo',
                ],
            ],
            'Foo::baz' => [
                'templateName' => '@baz',
                'variables' => [
                    'baz' => 'baz',
                ],
            ],
        ];

        $this->expectContentObjectConfigurationCalls(1);
        $this->expectContentObjectConfiguration(
            'HANDLEBARSTEMPLATE',
            [
                'templateName' => '@baz',
                'variables.' => [
                    'default' => 'foo',
                    'foo' => 'foo',
                    'baz' => 'baz',
                ],
                'format' => 'hbs',
            ],
        );

        $data = new Core\View\ViewFactoryData(request: $this->request, format: 'hbs');

        $actual = $this->subject->create($data);

        self::assertInstanceOf(Src\View\HandlebarsView::class, $actual);

        $actual->render();
    }

    #[Framework\Attributes\Test]
    public function createReturnsHandlebarsViewWithDefaultConfigurationIfProcessedTemplateNameIsEmpty(): void
    {
        $this->configurationManager->configuration['handlebars'] = [
            'Foo:boo' => [
                'templateName' => '@boo',
            ],
        ];

        $this->expectContentObjectConfigurationCalls(1);
        $this->expectContentObjectConfiguration(
            'HANDLEBARSTEMPLATE',
            [
                'templateName' => 'Foo/baz',
                'format' => 'hbs',
            ],
        );

        $data = new Core\View\ViewFactoryData(request: $this->request, format: 'hbs');

        $actual = $this->subject->create($data);

        self::assertInstanceOf(Src\View\HandlebarsView::class, $actual);

        $actual->render();
    }

    #[Framework\Attributes\Test]
    public function createReturnsDefaultHandlebarsViewIfOutsideOfExtbaseContext(): void
    {
        $request = $this->buildServerRequest();
        $request = $request->withAttribute('currentContentObject', $this->contentObjectRendererMock);

        $data = new Core\View\ViewFactoryData(
            templateRootPaths: [
                10 => 'EXT:foo/Resources/Private/Templates',
            ],
            partialRootPaths: [
                10 => 'EXT:foo/Resources/Private/Partials',
            ],
            layoutRootPaths: [
                10 => 'EXT:foo/Resources/Private/Layouts',
            ],
            templatePathAndFilename: '@foo',
            request: $request,
            format: 'hbs',
        );

        $this->expectContentObjectConfiguration(
            'HANDLEBARSTEMPLATE',
            [
                'templateName' => '@foo',
                'templateRootPaths.' => [
                    10 => 'EXT:foo/Resources/Private/Templates',
                ],
                'partialRootPaths.' => [
                    10 => 'EXT:foo/Resources/Private/Partials',
                ],
                'layoutRootPaths.' => [
                    10 => 'EXT:foo/Resources/Private/Layouts',
                ],
                'format' => 'hbs',
            ],
        );

        $actual = $this->subject->create($data);

        self::assertInstanceOf(Src\View\HandlebarsView::class, $actual);

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

        /* @phpstan-ignore assign.propertyType */
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
