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
    ];

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
    public function processThrowsExceptionIfConfiguredPreProcessorOrPostProcessorIsunsupported(): void
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
}
