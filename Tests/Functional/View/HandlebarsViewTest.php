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
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Core;
use TYPO3\CMS\Frontend;
use TYPO3\TestingFramework;

/**
 * HandlebarsViewTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\View\HandlebarsView::class)]
final class HandlebarsViewTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    protected bool $initializeDatabase = false;

    private Frontend\ContentObject\ContentObjectRenderer&Framework\MockObject\MockObject $contentObjectRendererMock;
    private Tests\Unit\Fixtures\Classes\DummyView $delegate;
    private Src\View\HandlebarsView $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->contentObjectRendererMock = $this->createMock(Frontend\ContentObject\ContentObjectRenderer::class);
        $this->delegate = new Tests\Unit\Fixtures\Classes\DummyView();
        $this->subject = new Src\View\HandlebarsView(
            $this->contentObjectRendererMock,
            new Core\TypoScript\TypoScriptService(),
            [
                'templateName' => '@foo',
            ],
            null,
            $this->delegate,
        );
    }

    #[Framework\Attributes\Test]
    public function assignAssignsSimpleVariableToContentObjectConfiguration(): void
    {
        $this->subject->assign('foo', 'baz');

        $this->expectContentObjectConfiguration([
            'variables.' => [
                'foo' => 'baz',
            ],
        ]);

        $this->subject->render();
    }

    #[Framework\Attributes\Test]
    public function assignAssignsArrayVariableToContentObjectConfiguration(): void
    {
        $this->subject->assign('foo', [
            'baz' => [
                'bar' => 'hello world',
            ],
        ]);

        $this->expectContentObjectConfiguration([
            'variables.' => [
                'foo.' => [
                    'baz.' => [
                        'bar' => 'hello world',
                    ],
                ],
            ],
        ]);

        $this->subject->render();
    }

    #[Framework\Attributes\Test]
    public function assignAssignsSettingsToContentObjectConfiguration(): void
    {
        $this->subject->assign('settings', ['foo' => 'baz']);
        $this->subject->assign('settings', ['baz' => 'foo']);

        $this->expectContentObjectConfiguration([
            'settings.' => [
                'foo' => 'baz',
                'baz' => 'foo',
            ],
        ]);

        $this->subject->render();
    }

    #[Framework\Attributes\Test]
    public function assignPassesVariableToDelegateView(): void
    {
        $this->subject->assign('foo', 'baz');

        self::assertSame(['foo' => 'baz'], $this->delegate->assignedVariables);
    }

    #[Framework\Attributes\Test]
    public function assignMultipleAssignsAllVariablesToContentObjectConfiguration(): void
    {
        $this->subject->assignMultiple([
            'baz' => 'foo',
            'foo' => 'baz',
        ]);

        $this->expectContentObjectConfiguration([
            'variables.' => [
                'baz' => 'foo',
                'foo' => 'baz',
            ],
        ]);

        $this->subject->render();
    }

    #[Framework\Attributes\Test]
    public function renderRendersHandlebarsTemplateContentObjectWithContentObjectConfiguration(): void
    {
        $this->expectContentObjectConfiguration(return: 'foo');

        self::assertSame('foo', $this->subject->render());
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\AllowMockObjectsWithoutExpectations]
    public function renderUsesClonedContentObjectRendererWithConfiguredRequest(): void
    {
        $contentObject = new Src\Tests\Unit\Fixtures\Classes\DummyContentObject();

        $container = $this->getContainer();

        self::assertInstanceOf(DependencyInjection\Container::class, $container);

        $container->set('HANDLEBARSTEMPLATE', $contentObject);
        $container->set(Frontend\ContentObject\ContentObjectFactory::class, new Frontend\ContentObject\ContentObjectFactory($container));

        $request = new Core\Http\ServerRequest();
        $contentObjectRenderer = $this->get(Frontend\ContentObject\ContentObjectRenderer::class);
        $contentObjectRenderer->setRequest($request);

        $subject = new Src\View\HandlebarsView(
            $contentObjectRenderer,
            new Core\TypoScript\TypoScriptService(),
            [
                'templateName' => '@foo',
            ],
            $request,
        );

        $expectedRequest = clone $request;
        $expectedRequest = $expectedRequest->withAttribute('currentContentObject', $contentObjectRenderer);

        $expectedContentObjectRenderer = clone $contentObjectRenderer;
        $expectedContentObjectRenderer->setRequest($request);

        self::assertSame('foo', $subject->render());
        self::assertEquals($expectedRequest, $contentObject->getRequest());
        self::assertEquals($expectedContentObjectRenderer, $contentObject->getContentObjectRenderer());
        self::assertNotSame($contentObjectRenderer, $contentObject->getContentObjectRenderer());
    }

    #[Framework\Attributes\Test]
    public function delegateRenderingReturnsNullOnMissingDelegate(): void
    {
        $subject = new Src\View\HandlebarsView(
            $this->contentObjectRendererMock,
            new Core\TypoScript\TypoScriptService(),
            [],
        );

        self::assertNull($subject->delegateRendering());
    }

    #[Framework\Attributes\Test]
    public function delegateRenderingReturnsRenderedTemplateFromDelegate(): void
    {
        $this->delegate->expectedTemplateResult = 'foo';

        self::assertSame('foo', $this->subject->delegateRendering());
    }

    #[Framework\Attributes\Test]
    public function setTemplateNameOverridesTemplateNameInContentObjectConfiguration(): void
    {
        $this->subject->setTemplateName('@baz');

        $this->expectContentObjectConfiguration([
            'templateName' => '@baz',
        ]);

        $this->subject->render();
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\AllowMockObjectsWithoutExpectations]
    public function getTemplateNameReturnsTemplateNameFromContentObjectConfiguration(): void
    {
        self::assertSame('@foo', $this->subject->getTemplateName());
    }

    /**
     * @param array<string, mixed> $contentObjectConfiguration
     */
    private function expectContentObjectConfiguration(array $contentObjectConfiguration = [], string $return = ''): void
    {
        if (!isset($contentObjectConfiguration['templateName'])) {
            $contentObjectConfiguration['templateName'] = '@foo';
        }

        $this->contentObjectRendererMock->expects(self::once())
            ->method('cObjGetSingle')
            ->with('HANDLEBARSTEMPLATE', $contentObjectConfiguration)
            ->willReturn($return)
        ;
    }
}
