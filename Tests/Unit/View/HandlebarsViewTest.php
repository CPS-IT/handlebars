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

namespace CPSIT\Typo3Handlebars\Tests\Unit\View;

use CPSIT\Typo3Handlebars as Src;
use PHPUnit\Framework;
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
final class HandlebarsViewTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Frontend\ContentObject\ContentObjectRenderer&Framework\MockObject\MockObject $contentObjectRendererMock;
    private Src\View\HandlebarsView $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->contentObjectRendererMock = $this->createMock(Frontend\ContentObject\ContentObjectRenderer::class);
        $this->subject = new Src\View\HandlebarsView(
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
    public function setTemplateNameOverridesTemplateNameInContentObjectConfiguration(): void
    {
        $this->subject->setTemplateName('@baz');

        $this->expectContentObjectConfiguration([
            'templateName' => '@baz',
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
