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
use TYPO3\CMS\Core;
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

    private Log\Test\TestLogger $logger;
    private Src\DataProcessing\ProcessVariablesProcessor $subject;
    private Frontend\ContentObject\ContentObjectRenderer $contentObjectRenderer;

    public function setUp(): void
    {
        parent::setUp();

        $request = $this->buildServerRequest();

        $this->logger = new Log\Test\TestLogger();
        $this->subject = new Src\DataProcessing\ProcessVariablesProcessor(
            $this->logger,
            new Core\TypoScript\TypoScriptService(),
        );
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
    public function processUsesConfiguredDataIfNoDataSourcesAreDefined(): void
    {
        $processorConfiguration = [
            'data.' => [
                'foo' => 'baz',
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

        self::assertSame($expected, $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, []));
    }

    #[Framework\Attributes\Test]
    public function processUsesDataFromProcessedDataIfNoDataSourcesAreDefined(): void
    {
        $processorConfiguration = [
            'variables.' => [
                'foo' => 'TEXT',
                'foo.' => [
                    'field' => 'foo',
                ],
            ],
        ];
        $processedData = [
            'data' => [
                'foo' => 'baz',
            ],
        ];

        $expected = [
            'foo' => 'baz',
        ];

        self::assertSame($expected, $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, $processedData));
    }

    /**
     * @return \Generator<string, array{Src\DataProcessing\ProcessorDataSource, array<string, mixed>}>
     */
    public static function processAcceptsSingleDataSourceDataProvider(): \Generator
    {
        yield 'content object configuration' => [
            Src\DataProcessing\ProcessorDataSource::ContentObjectConfiguration,
            ['foo' => 'COC-BAZ'],
        ];
        yield 'content object renderer' => [
            Src\DataProcessing\ProcessorDataSource::ContentObjectRenderer,
            ['foo' => 'COR-BAZ'],
        ];
        yield 'processed data' => [
            Src\DataProcessing\ProcessorDataSource::ProcessedData,
            ['foo' => 'PD-BAZ'],
        ];
    }

    /**
     * @param array<string, mixed> $expected
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('processAcceptsSingleDataSourceDataProvider')]
    public function processAcceptsSingleDataSource(
        Src\DataProcessing\ProcessorDataSource $dataSource,
        array $expected,
    ): void {
        $this->contentObjectRenderer->data = [
            'foo' => 'COR-BAZ',
        ];

        $contentObjectConfiguration = [
            'foo' => 'COC-BAZ',
        ];
        $processorConfiguration = [
            'dataSource' => $dataSource->value,
            'variables.' => [
                'foo' => 'TEXT',
                'foo.' => [
                    'field' => 'foo',
                ],
            ],
        ];
        $processedData = [
            'foo' => 'PD-BAZ',
        ];

        self::assertSame(
            $expected,
            $this->subject->process($this->contentObjectRenderer, $contentObjectConfiguration, $processorConfiguration, $processedData),
        );
    }

    /**
     * @return \Generator<string, array{array<int, string>, array<string, mixed>}>
     */
    public static function processAcceptsDifferentDataSourcesDataProvider(): \Generator
    {
        yield 'no data sources' => [
            [],
            [],
        ];
        yield 'single data source' => [
            [Src\DataProcessing\ProcessorDataSource::ContentObjectRenderer->value],
            ['foo' => 'COR-BAZ'],
        ];
        yield 'multiple data sources' => [
            [
                30 => 'contentObjectConfiguration:bar',
                20 => 'contentObjectRenderer',
                10 => 'processedData',
            ],
            ['foo' => 'COC-BAZ'],
        ];
    }

    /**
     * @param array<int, string> $dataSources
     * @param array<string, mixed> $expected
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('processAcceptsDifferentDataSourcesDataProvider')]
    public function processAcceptsDifferentDataSources(array $dataSources, array $expected): void
    {
        $this->contentObjectRenderer->data = [
            'foo' => 'COR-BAZ',
        ];

        $contentObjectConfiguration = [
            'bar.' => [
                'foo' => 'COC-BAZ',
            ],
        ];
        $processorConfiguration = [
            'dataSource.' => $dataSources,
            'variables.' => [
                'foo' => 'TEXT',
                'foo.' => [
                    'field' => 'foo',
                ],
            ],
        ];
        $processedData = [
            'foo' => 'PD-BAZ',
        ];

        self::assertSame(
            $expected,
            $this->subject->process($this->contentObjectRenderer, $contentObjectConfiguration, $processorConfiguration, $processedData),
        );
    }

    #[Framework\Attributes\Test]
    public function processLogsUsageOfInvalidDataSourceKeyword(): void
    {
        $this->contentObjectRenderer->start(['uid' => 123], 'tt_content');

        $processorConfiguration = [
            'dataSource' => 'foo',
        ];

        self::assertSame([], $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, []));

        self::assertTrue(
            $this->logger->hasWarning([
                'message' => 'Invalid processor data source keyword "{source}" passed to "process-variables" data processor (while processing {table}:{uid}).',
                'context' => [
                    'source' => 'foo',
                    'table' => 'tt_content',
                    'uid' => 123,
                ],
            ]),
        );
    }

    #[Framework\Attributes\Test]
    public function processHandlesDataSourcesWithConfiguredPath(): void
    {
        $processorConfiguration = [
            'dataSource' => 'processedData:foo.baz',
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

        $expected = [
            'bar' => 'foo',
        ];

        self::assertSame(
            $expected,
            $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, $processedData),
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
                'message' => 'Invalid path "{path}" for processor data source "{source}" passed to "process-variables" data processor (while processing {table}:{uid}).',
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
}
