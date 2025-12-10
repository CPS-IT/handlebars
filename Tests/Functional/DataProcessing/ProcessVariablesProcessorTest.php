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

namespace CPSIT\Typo3Handlebars\Tests\Functional\DataProcessing;

use CPSIT\Typo3Handlebars as Src;
use CPSIT\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Frontend;
use TYPO3\TestingFramework;

/**
 * ProcessVariablesProcessorTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\DataProcessing\ProcessVariablesProcessor::class)]
final class ProcessVariablesProcessorTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\FrontendRequestTrait;

    private Src\DataProcessing\ProcessVariablesProcessor $subject;
    private Frontend\ContentObject\ContentObjectRenderer $contentObjectRenderer;

    public function setUp(): void
    {
        parent::setUp();

        $request = $this->buildServerRequest();

        $this->subject = new Src\DataProcessing\ProcessVariablesProcessor();
        $this->contentObjectRenderer = new Frontend\ContentObject\ContentObjectRenderer();
        $this->contentObjectRenderer->setRequest($request);
        $this->get(Extbase\Configuration\ConfigurationManagerInterface::class)->setRequest($request);
    }

    #[Framework\Attributes\Test]
    public function processDoesNothingIfNoVariablesAreConfigured(): void
    {
        self::assertSame([], $this->subject->process($this->contentObjectRenderer, [], [], []));
    }

    #[Framework\Attributes\Test]
    public function processDoesNothingIfGivenConditionDoesNotMatch(): void
    {
        $this->contentObjectRenderer->data = [
            'foo' => '',
        ];

        $processorConfiguration = [
            'variables.' => [
                'foo' => ' TEXT',
                'foo.' => [
                    'field' => 'foo',
                ],
            ],
            'if.' => [
                'isTrue.' => [
                    'field' => 'foo',
                ],
            ],
        ];

        self::assertSame([], $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, []));
    }

    #[Framework\Attributes\Test]
    public function processReturnsProcessedVariablesAsProcessedData(): void
    {
        $this->contentObjectRenderer->data = [
            'foo' => 'baz',
        ];

        $processorConfiguration = [
            'variables.' => [
                'foo' => 'TEXT',
                'foo.' => [
                    'field' => 'foo',
                ],
            ],
        ];
        $processedData = [
            'baz' => 'foo',
        ];

        $expected = [
            'foo' => 'baz',
        ];

        self::assertSame($expected, $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, $processedData));
    }

    #[Framework\Attributes\Test]
    public function processReturnsProcessedVariablesAsTargetVariableInProcessedData(): void
    {
        $this->contentObjectRenderer->data = [
            'foo' => 'baz',
        ];

        $processorConfiguration = [
            'as' => 'target',
            'variables.' => [
                'foo' => 'TEXT',
                'foo.' => [
                    'field' => 'foo',
                ],
            ],
        ];
        $processedData = [
            'baz' => 'foo',
        ];

        $expected = [
            'baz' => 'foo',
            'target' => [
                'foo' => 'baz',
            ],
        ];

        self::assertSame($expected, $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, $processedData));
    }

    #[Framework\Attributes\Test]
    public function processMergesProcessedVariablesWithProcessedData(): void
    {
        $this->contentObjectRenderer->data = [
            'foo' => 'baz',
        ];

        $processorConfiguration = [
            'merge' => '1',
            'variables.' => [
                'foo' => 'TEXT',
                'foo.' => [
                    'field' => 'foo',
                ],
            ],
        ];
        $processedData = [
            'baz' => 'foo',
        ];

        $expected = [
            'baz' => 'foo',
            'foo' => 'baz',
        ];

        self::assertSame($expected, $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, $processedData));
    }

    #[Framework\Attributes\Test]
    public function processMergesProcessedVariablesWithTargetVariableInProcessedData(): void
    {
        $this->contentObjectRenderer->data = [
            'foo' => 'baz',
        ];

        $processorConfiguration = [
            'as' => 'target',
            'merge' => '1',
            'variables.' => [
                'foo' => 'TEXT',
                'foo.' => [
                    'field' => 'foo',
                ],
            ],
        ];
        $processedData = [
            'target' => [
                'baz' => 'foo',
            ],
        ];

        $expected = [
            'target' => [
                'baz' => 'foo',
                'foo' => 'baz',
            ],
        ];

        self::assertSame($expected, $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, $processedData));
    }
}
