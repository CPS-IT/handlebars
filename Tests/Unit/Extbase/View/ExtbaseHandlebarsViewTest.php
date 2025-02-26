<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2025 Elias Häußler <e.haeussler@familie-redlich.de>
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

namespace Fr\Typo3Handlebars\Tests\Unit\Extbase\View;

use Fr\Typo3Handlebars as Src;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Frontend;
use TYPO3\TestingFramework;

/**
 * ExtbaseHandlebarsViewTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Extbase\View\ExtbaseHandlebarsView::class)]
final class ExtbaseHandlebarsViewTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Frontend\ContentObject\ContentObjectRenderer&Framework\MockObject\MockObject $contentObjectRendererMock;
    private Src\Extbase\View\ExtbaseHandlebarsView $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->contentObjectRendererMock = $this->createMock(Frontend\ContentObject\ContentObjectRenderer::class);
        $this->subject = new Src\Extbase\View\ExtbaseHandlebarsView(
            $this->contentObjectRendererMock,
            new Core\TypoScript\TypoScriptService(),
            [
                'templateName' => '@foo',
            ],
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
    public function renderSectionReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->renderSection('foo'));
    }

    #[Framework\Attributes\Test]
    public function renderPartialReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->renderPartial('foo', 'baz', []));
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
    public function setTemplateNameFromRequestOverridesExtractedTemplateNameInContentObjectConfiguration(): void
    {
        $requestMock = $this->createMock(Extbase\Mvc\RequestInterface::class);
        $requestMock->method('getControllerName')->willReturn('Foo');
        $requestMock->method('getControllerActionName')->willReturn('Baz');

        $this->subject->setTemplateNameFromRequest($requestMock);

        $this->expectContentObjectConfiguration([
            'templateName' => 'Foo/Baz',
        ]);

        $this->subject->render();
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
