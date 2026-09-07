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
 * ProcessEachProcessorTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\DataProcessing\ProcessEachProcessor::class)]
final class ProcessEachProcessorTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\FrontendRequestTrait;

    protected array $testExtensionsToLoad = [
        'handlebars',
        'typed_extconf',
    ];

    protected bool $initializeDatabase = false;

    private Log\Test\TestLogger $logger;
    private Src\DataProcessing\ProcessEachProcessor $subject;
    private Frontend\ContentObject\ContentObjectRenderer $contentObjectRenderer;

    public function setUp(): void
    {
        parent::setUp();

        $request = $this->buildServerRequest();

        $this->logger = new Log\Test\TestLogger();
        $this->subject = new Src\DataProcessing\ProcessEachProcessor(
            $this->get(Frontend\ContentObject\ContentDataProcessor::class),
            $this->get(Src\DataProcessing\DataSource\DataSourceProvider::class),
            $this->logger,
        );
        $this->contentObjectRenderer = $this->get(Frontend\ContentObject\ContentObjectRenderer::class);
        $this->contentObjectRenderer->setRequest($request);
        $this->get(Extbase\Configuration\ConfigurationManagerInterface::class)->setRequest($request);
        $this->contentObjectRenderer->start(['uid' => 123], 'tt_content');
    }

    #[Framework\Attributes\Test]
    public function processReturnsProcessedDataUnmodifiedIfNoIterableDataCanBeResolved(): void
    {
        $processedData = [
            'foo' => 'bar',
        ];

        self::assertSame(
            $processedData,
            $this->subject->process($this->contentObjectRenderer, [], [], $processedData),
        );
    }

    #[Framework\Attributes\Test]
    public function processReturnsProcessedDataUnmodifiedIfResolvedDataIsNotIterable(): void
    {
        $processedData = [
            'data' => 'not-iterable',
        ];

        self::assertSame(
            $processedData,
            $this->subject->process($this->contentObjectRenderer, [], [], $processedData),
        );
    }

    #[Framework\Attributes\Test]
    public function processResolvesIterableDataFromInlineConfigurationAndPreservesOriginalKeys(): void
    {
        $processorConfiguration = [
            'data.' => [
                'x' => 'foo',
                'y' => 'bar',
            ],
        ];

        $expected = [
            'result' => [
                'x' => 'foo',
                'y' => 'bar',
            ],
        ];

        self::assertSame(
            $expected,
            $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, []),
        );
    }

    #[Framework\Attributes\Test]
    public function processResolvesIterableDataFromProcessedDataFallbackIfNoDataSourceIsConfigured(): void
    {
        $processedData = [
            'data' => [
                'foo',
                'bar',
            ],
        ];

        $expected = [
            'data' => [
                'foo',
                'bar',
            ],
            'result' => [
                'foo',
                'bar',
            ],
        ];

        self::assertSame(
            $expected,
            $this->subject->process($this->contentObjectRenderer, [], [], $processedData),
        );
    }

    #[Framework\Attributes\Test]
    public function processResolvesIterableDataFromConfiguredDataSourceAndAppliesConfiguredTargetKey(): void
    {
        $processorConfiguration = [
            'dataSource' => 'processedData:files',
            'as' => 'processedFiles',
        ];
        $processedData = [
            'files' => [
                'a' => 'foo',
                'b' => 'bar',
            ],
        ];

        $expected = [
            'files' => [
                'a' => 'foo',
                'b' => 'bar',
            ],
            'processedFiles' => [
                'a' => 'foo',
                'b' => 'bar',
            ],
        ];

        self::assertSame(
            $expected,
            $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, $processedData),
        );
    }

    #[Framework\Attributes\Test]
    public function processLogsWarningAndReturnsProcessedDataUnmodifiedIfConfiguredDataSourceKeywordIsNotSupported(): void
    {
        $processorConfiguration = [
            'dataSource' => 'foo',
        ];
        $processedData = [
            'bar' => 'baz',
        ];

        self::assertSame(
            $processedData,
            $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, $processedData),
        );
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
    public function processLogsWarningAndReturnsProcessedDataUnmodifiedIfConfiguredDataSourcePathIsMissing(): void
    {
        $processorConfiguration = [
            'dataSource' => 'processedData:foo.bar',
        ];
        $processedData = [
            'foo' => [
                'baz' => [
                    'bar' => 'foo',
                ],
            ],
        ];

        self::assertSame(
            $processedData,
            $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, $processedData),
        );
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
    public function processAppliesVariablesConfigurationToEachItemUsingCurrentValue(): void
    {
        $processorConfiguration = [
            'data.' => [
                'first' => 'foo',
                'second' => 'bar',
            ],
            'variables.' => [
                'label' => 'TEXT',
                'label.' => [
                    'current' => 1,
                    'case' => 'upper',
                ],
            ],
        ];

        $expected = [
            'result' => [
                'first' => ['label' => 'FOO'],
                'second' => ['label' => 'BAR'],
            ],
        ];

        self::assertSame(
            $expected,
            $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, []),
        );
    }

    #[Framework\Attributes\Test]
    public function processExposesEachItemAsValueToNestedDataProcessingChain(): void
    {
        $foo = new Tests\Functional\Fixtures\Classes\DummyObject('foo');
        $bar = new Tests\Functional\Fixtures\Classes\DummyObject('bar');

        $processorConfiguration = [
            'data.' => [
                'first' => $foo,
                'second' => $bar,
            ],
            'dataProcessing.' => [
                '10' => 'object-access',
                '10.' => [
                    'object' => 'contentObjectConfiguration:currentValue',
                    'path' => 'name',
                    'as' => 'name',
                ],
            ],
        ];

        $expected = [
            'result' => [
                'first' => ['name' => 'foo'],
                'second' => ['name' => 'bar'],
            ],
        ];

        self::assertSame(
            $expected,
            $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, []),
        );
    }

    #[Framework\Attributes\Test]
    public function processMergesNestedDataProcessingResultOverVariablesResultForSameItem(): void
    {
        $processorConfiguration = [
            'data.' => [
                'item' => 'foo',
            ],
            'variables.' => [
                'label' => 'TEXT',
                'label.' => [
                    'value' => 'from-variables',
                ],
            ],
            'dataProcessing.' => [
                '10' => 'process-variables',
                '10.' => [
                    'merge' => '1',
                    'variables.' => [
                        'label' => 'TEXT',
                        'label.' => [
                            'value' => 'from-dataProcessing',
                        ],
                    ],
                ],
            ],
        ];

        $expected = [
            'result' => [
                'item' => [
                    'label' => 'from-dataProcessing',
                ],
            ],
        ];

        self::assertSame(
            $expected,
            $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, []),
        );
    }

    #[Framework\Attributes\Test]
    public function processRestoresCurrentContentObjectValueAfterProcessing(): void
    {
        $this->contentObjectRenderer->setCurrentVal('original');

        $processorConfiguration = [
            'data.' => [
                'first' => 'foo',
                'second' => 'bar',
            ],
        ];

        $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, []);

        self::assertSame('original', $this->contentObjectRenderer->getCurrentVal());
    }

    #[Framework\Attributes\Test]
    public function processRestoresCurrentContentObjectValueEvenIfProcessingThrowsException(): void
    {
        $this->contentObjectRenderer->setCurrentVal('original');

        $processorConfiguration = [
            'data.' => [
                'first' => 'foo',
            ],
            'variables.' => [
                'current' => 'TEXT',
                'current.' => [
                    'value' => 'not-allowed',
                ],
            ],
        ];

        try {
            $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, []);
            self::fail('Expected exception was not thrown.');
        } catch (Src\Exception\ReservedVariableCannotBeUsed) {
            // Expected exception, ignore
        }

        self::assertSame('original', $this->contentObjectRenderer->getCurrentVal());
    }
}
