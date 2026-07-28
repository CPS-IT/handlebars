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
 * IterableToArrayProcessorTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\DataProcessing\IterableToArrayProcessor::class)]
final class IterableToArrayProcessorTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\FrontendRequestTrait;

    protected array $testExtensionsToLoad = [
        'handlebars',
        'typed_extconf',
    ];

    private Log\Test\TestLogger $logger;
    private Src\DataProcessing\IterableToArrayProcessor $subject;
    private Frontend\ContentObject\ContentObjectRenderer $contentObjectRenderer;

    public function setUp(): void
    {
        parent::setUp();

        $request = $this->buildServerRequest();

        $this->logger = new Log\Test\TestLogger();
        $this->subject = new Src\DataProcessing\IterableToArrayProcessor(
            $this->logger,
            $this->get(Frontend\ContentObject\ContentDataProcessor::class),
        );
        $this->contentObjectRenderer = $this->get(Frontend\ContentObject\ContentObjectRenderer::class);
        $this->contentObjectRenderer->setRequest($request);
        $this->get(Extbase\Configuration\ConfigurationManagerInterface::class)->setRequest($request);
        $this->contentObjectRenderer->start(['uid' => 123], 'tt_content');
    }

    #[Framework\Attributes\Test]
    public function processLogsWarningAndReturnsProcessedDataUnmodifiedIfIterableSourceIsNotConfigured(): void
    {
        self::assertSame([], $this->subject->process($this->contentObjectRenderer, [], [], []));
        self::assertTrue(
            $this->logger->hasWarning([
                'message' => 'Invalid iterable source configured for "iterable-to-array" data processor while processing {table}:{uid}.',
                'context' => [
                    'table' => 'tt_content',
                    'uid' => 123,
                ],
            ]),
        );
    }

    #[Framework\Attributes\Test]
    public function processLogsWarningAndReturnsProcessedDataUnmodifiedIfIterableSourceIsEmpty(): void
    {
        $processorConfiguration = [
            'iterable' => '',
        ];

        self::assertSame([], $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, []));
        self::assertTrue(
            $this->logger->hasWarning([
                'message' => 'Invalid iterable source configured for "iterable-to-array" data processor while processing {table}:{uid}.',
                'context' => [
                    'table' => 'tt_content',
                    'uid' => 123,
                ],
            ]),
        );
    }

    #[Framework\Attributes\Test]
    public function processLogsWarningAndReturnsProcessedDataUnmodifiedIfConfiguredValueIsNotIterable(): void
    {
        $processorConfiguration = [
            'iterable' => 'someValue',
        ];
        $processedData = [
            'someValue' => new Tests\Functional\Fixtures\Classes\DummyObject('foo'),
        ];

        self::assertSame(
            $processedData,
            $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, $processedData),
        );
        self::assertTrue(
            $this->logger->hasWarning([
                'message' => 'Configured value at "{iterableSource}" is not iterable while processing {table}:{uid}.',
                'context' => [
                    'iterableSource' => 'someValue',
                    'table' => 'tt_content',
                    'uid' => 123,
                ],
            ]),
        );
    }

    #[Framework\Attributes\Test]
    public function processConvertsPlainArrayToListByDefault(): void
    {
        $processorConfiguration = [
            'iterable' => 'someArray',
        ];
        $processedData = [
            'someArray' => [
                'foo' => 'a',
                'bar' => 'b',
            ],
        ];

        $expected = [
            'someArray' => [
                'foo' => 'a',
                'bar' => 'b',
            ],
            'result' => ['a', 'b'],
        ];

        self::assertSame(
            $expected,
            $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, $processedData),
        );
    }

    #[Framework\Attributes\Test]
    public function processPreservesKeysIfConfigured(): void
    {
        $processorConfiguration = [
            'iterable' => 'someArray',
            'preserveKeys' => '1',
        ];
        $processedData = [
            'someArray' => [
                'foo' => 'a',
                'bar' => 'b',
            ],
        ];

        $expected = [
            'someArray' => [
                'foo' => 'a',
                'bar' => 'b',
            ],
            'result' => [
                'foo' => 'a',
                'bar' => 'b',
            ],
        ];

        self::assertSame(
            $expected,
            $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, $processedData),
        );
    }

    /**
     * @return \Generator<string, array{iterable<mixed>, list<mixed>}>
     */
    public static function processConvertsIterableToArrayDataProvider(): \Generator
    {
        $object1 = new Tests\Functional\Fixtures\Classes\DummyObject('foo');
        $object2 = new Tests\Functional\Fixtures\Classes\DummyObject('baz');

        $storage = new Extbase\Persistence\ObjectStorage();
        $storage->attach($object1);
        $storage->attach($object2);

        yield 'ObjectStorage' => [$storage, [$object1, $object2]];
        yield 'Generator' => [self::yieldItems(), ['a', 'b']];
    }

    /**
     * @param iterable<mixed> $iterable
     * @param list<mixed> $expectedItems
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('processConvertsIterableToArrayDataProvider')]
    public function processConvertsIterableToArray(iterable $iterable, array $expectedItems): void
    {
        $processorConfiguration = [
            'iterable' => 'someIterable',
        ];
        $processedData = [
            'someIterable' => $iterable,
        ];

        $expected = [
            'someIterable' => $iterable,
            'result' => $expectedItems,
        ];

        self::assertSame(
            $expected,
            $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, $processedData),
        );
    }

    #[Framework\Attributes\Test]
    public function processDoesNotApplyDataProcessingToItemsIfNotConfigured(): void
    {
        $object = new Tests\Functional\Fixtures\Classes\DummyObject('foo');

        $processorConfiguration = [
            'iterable' => 'someArray',
        ];
        $processedData = [
            'someArray' => [$object],
        ];

        $expected = [
            'someArray' => [$object],
            'result' => [$object],
        ];

        self::assertSame(
            $expected,
            $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, $processedData),
        );
    }

    #[Framework\Attributes\Test]
    public function processAppliesConfiguredDataProcessingToEachItem(): void
    {
        $object1 = new Tests\Functional\Fixtures\Classes\DummyObject('foo');
        $object2 = new Tests\Functional\Fixtures\Classes\DummyObject('baz');

        $processorConfiguration = [
            'iterable' => 'someArray',
            'as' => 'items',
            'dataProcessing.' => [
                '10' => 'object-access',
                '10.' => [
                    'object' => 'data',
                    'path' => 'name',
                    'as' => 'name',
                ],
            ],
        ];
        $processedData = [
            'someArray' => [$object1, $object2],
        ];

        $expected = [
            'someArray' => [$object1, $object2],
            'items' => [
                [
                    'data' => $object1,
                    'name' => 'foo',
                ],
                [
                    'data' => $object2,
                    'name' => 'baz',
                ],
            ],
        ];

        self::assertSame(
            $expected,
            $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, $processedData),
        );
    }

    /**
     * @return \Generator<string>
     */
    private static function yieldItems(): \Generator
    {
        yield 'foo' => 'a';
        yield 'bar' => 'b';
    }
}
