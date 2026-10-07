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
use Psr\Log;
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

    protected array $testExtensionsToLoad = [
        'handlebars',
        'typed_extconf',
    ];

    protected bool $initializeDatabase = false;

    private Log\Test\TestLogger $logger;
    private Src\DataProcessing\ProcessVariablesProcessor $subject;
    private Frontend\ContentObject\ContentObjectRenderer $contentObjectRenderer;

    public function setUp(): void
    {
        parent::setUp();

        $request = $this->buildServerRequest();

        $this->logger = new Log\Test\TestLogger();
        $this->subject = new Src\DataProcessing\ProcessVariablesProcessor(
            $this->get(Src\DataProcessing\DataSource\DataSourceProvider::class),
            $this->logger,
        );
        $this->contentObjectRenderer = $this->get(Frontend\ContentObject\ContentObjectRenderer::class);
        $this->contentObjectRenderer->setRequest($request);
        $this->get(Extbase\Configuration\ConfigurationManagerInterface::class)->setRequest($request);
    }

    #[Framework\Attributes\Test]
    public function processDoesNothingIfNoVariablesAreConfigured(): void
    {
        self::assertSame([], $this->subject->process($this->contentObjectRenderer, [], [], []));
    }

    #[Framework\Attributes\Test]
    public function processTriggersConfiguredPreProcessors(): void
    {
        $processorConfiguration = [
            'variables.' => [],
            'preProcessing.' => [
                '30' => Tests\Functional\Fixtures\Classes\DummyPreProcessor::class,
                '10' => Tests\Functional\Fixtures\Classes\DummyPreProcessor::class,
                '20' => Tests\Functional\Fixtures\Classes\DummyPreProcessor::class,
            ],
        ];

        self::assertSame(
            ['foo' => 3],
            $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, []),
        );
    }

    #[Framework\Attributes\Test]
    public function processThrowsExceptionIfConfiguredPreProcessorOrPostProcessorIsUnsupported(): void
    {
        $processorConfiguration = [
            'variables.' => [],
            'preProcessing.' => [
                '10' => self::class,
            ],
        ];

        $this->expectExceptionObject(
            new Src\Exception\ConfiguredProcessorIsUnsupported(self::class),
        );

        $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, []);
    }

    #[Framework\Attributes\Test]
    public function processLogsUsageOfMissingDataSource(): void
    {
        $this->contentObjectRenderer->start(['uid' => 123], 'tt_content');

        $processorConfiguration = [
            'dataSource' => Src\DataProcessing\DataSource\DataSource::ProcessedData->value,
            'variables.' => [],
            'preProcessing.' => [
                '10' => Tests\Functional\Fixtures\Classes\DataSourceCollectionManipulatingPreProcessor::class,
            ],
        ];

        self::assertSame([], $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, []));
        self::assertTrue(
            $this->logger->hasWarning([
                'message' => 'No data provided for data source "{source}" while processing {table}:{uid}.',
                'context' => [
                    'source' => Src\DataProcessing\DataSource\DataSource::ProcessedData->value,
                    'table' => 'tt_content',
                    'uid' => 123,
                ],
            ]),
        );
    }

    #[Framework\Attributes\Test]
    public function processLogsUsageOfInvalidDataSourceKeyword(): void
    {
        $this->contentObjectRenderer->start(['uid' => 123], 'tt_content');

        $processorConfiguration = [
            'dataSource' => 'foo',
            'variables.' => [],
        ];

        self::assertSame([], $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, []));
        self::assertTrue(
            $this->logger->hasWarning([
                'message' => 'Invalid data source keyword "{source}" passed while processing {table}:{uid}.',
                'context' => [
                    'source' => 'foo',
                    'table' => 'tt_content',
                    'uid' => 123,
                ],
            ]),
        );
    }

    #[Framework\Attributes\Test]
    public function processLogsUsageOfInvalidDataSourcePath(): void
    {
        $this->contentObjectRenderer->start(['uid' => 123], 'tt_content');

        $processorConfiguration = [
            'dataSource' => 'processedData:foo.bar',
            'variables.' => [
                'bar' => 'TEXT',
                'bar.' => [
                    'field' => 'bar',
                ],
            ],
        ];
        $processedData = [
            'foo' => [
                'baz' => [
                    'bar' => 'foo',
                ],
            ],
        ];

        self::assertSame([], $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, $processedData));
        self::assertTrue(
            $this->logger->hasWarning([
                'message' => 'Invalid path "{path}" for data source "{source}" passed while processing {table}:{uid}.',
                'context' => [
                    'path' => 'foo.bar',
                    'source' => 'processedData',
                    'table' => 'tt_content',
                    'uid' => 123,
                ],
            ]),
        );
    }

    #[Framework\Attributes\Test]
    public function processRespectsGivenDataSource(): void
    {
        $processorConfiguration = [
            'dataSource' => 'processedData',
            'variables.' => [
                'bar' => 'TEXT',
                'bar.' => [
                    'field' => 'bar',
                ],
            ],
        ];
        $processedData = [
            'bar' => 'foo',
        ];

        $expected = [
            'bar' => 'foo',
        ];

        self::assertSame(
            $expected,
            $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, $processedData),
        );
    }

    #[Framework\Attributes\Test]
    public function processUsesCurrentContentObjectValueAsDataIfDataSourceCurrentIsConfigured(): void
    {
        $this->contentObjectRenderer->setCurrentVal([
            'foo' => 'baz',
        ]);

        $processorConfiguration = [
            'dataSource.' => [
                'current' => '1',
            ],
            'variables.' => [
                'foo' => 'TEXT',
                'foo.' => [
                    'field' => 'foo',
                ],
            ],
        ];

        $expected = [
            'foo' => 'baz',
        ];

        self::assertSame(
            $expected,
            $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, []),
        );
    }

    #[Framework\Attributes\Test]
    public function processProvidesNonArrayDataAsCurrentValue(): void
    {
        $this->contentObjectRenderer->start(['uid' => 123, 'header' => 'outer'], 'tt_content');

        $processorConfiguration = [
            'dataSource' => 'processedData:foo',
            'variables.' => [
                'foo' => 'TEXT',
                'foo.' => [
                    'current' => '1',
                ],
                'header' => 'TEXT',
                'header.' => [
                    'field' => 'header',
                ],
            ],
        ];
        $processedData = [
            'foo' => 'baz',
        ];

        // "header" is empty (and therefore omitted), since the current record must not be used
        $expected = [
            'foo' => 'baz',
        ];

        self::assertSame(
            $expected,
            $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, $processedData),
        );
        self::assertTrue(
            $this->logger->hasDebug([
                'message' => 'Resolved data of type "{type}" is not an array and is therefore provided as current value while processing {table}:{uid}.',
                'context' => [
                    'type' => 'string',
                    'table' => 'tt_content',
                    'uid' => 123,
                ],
            ]),
        );
    }

    #[Framework\Attributes\Test]
    public function processProvidesStringableObjectDataAsCurrentValue(): void
    {
        $this->contentObjectRenderer->start(['uid' => 123, 'header' => 'outer'], 'tt_content');

        $processorConfiguration = [
            'dataSource' => 'processedData:foo',
            'variables.' => [
                'foo' => 'TEXT',
                'foo.' => [
                    'current' => '1',
                    'wrap' => '<b>|</b>',
                ],
                'header' => 'TEXT',
                'header.' => [
                    'field' => 'header',
                ],
            ],
        ];
        $processedData = [
            'foo' => new class implements \Stringable {
                public function __toString(): string
                {
                    return 'baz';
                }
            },
        ];

        // "header" is empty (and therefore omitted), since the current record must not be used
        $expected = [
            'foo' => '<b>baz</b>',
        ];

        self::assertSame(
            $expected,
            $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, $processedData),
        );
    }

    #[Framework\Attributes\Test]
    public function processProvidesObjectDataAsWrappedCurrentValue(): void
    {
        $this->contentObjectRenderer->start(['uid' => 123, 'header' => 'outer'], 'tt_content');
        $this->contentObjectRenderer->setCurrentVal('outer');

        $object = new Tests\Functional\Fixtures\Classes\DummyObject('foo');
        $processorConfiguration = [
            'dataSource' => 'processedData:foo',
            'variables.' => [
                'foo' => 'TEXT',
                'foo.' => [
                    'current' => '1',
                    'ifEmpty' => 'empty',
                ],
                'header' => 'TEXT',
                'header.' => [
                    'field' => 'header',
                ],
            ],
            'postProcessing.' => [
                '10' => Tests\Functional\Fixtures\Classes\CurrentValueExposingPostProcessor::class,
            ],
        ];
        $processedData = [
            'foo' => $object,
        ];

        $actual = $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, $processedData);

        // Object cannot be converted to string, hence "ifEmpty" applies. "header" is empty
        // (and therefore omitted), since the current record must not be used.
        self::assertSame('empty', $actual['foo'] ?? null);
        self::assertArrayNotHasKey('header', $actual);
        self::assertInstanceOf(Src\DataProcessing\DataSource\CurrentValue::class, $actual['currentValue'] ?? null);
        self::assertSame($object, $actual['currentValue']->value);
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
    public function processTriggersConfiguredPostProcessors(): void
    {
        $processorConfiguration = [
            'variables.' => [],
            'postProcessing.' => [
                '30' => Tests\Functional\Fixtures\Classes\DummyPostProcessor::class,
                '10' => Tests\Functional\Fixtures\Classes\DummyPostProcessor::class,
                '20' => Tests\Functional\Fixtures\Classes\DummyPostProcessor::class,
            ],
        ];

        self::assertSame(
            ['foo' => -3],
            $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, []),
        );
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
