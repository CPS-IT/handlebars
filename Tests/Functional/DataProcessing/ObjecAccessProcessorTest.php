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
 * ObjecAccessProcessorTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\DataProcessing\ObjecAccessProcessor::class)]
final class ObjecAccessProcessorTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\FrontendRequestTrait;

    private Log\Test\TestLogger $logger;
    private Src\DataProcessing\ObjecAccessProcessor $subject;
    private Frontend\ContentObject\ContentObjectRenderer $contentObjectRenderer;

    public function setUp(): void
    {
        parent::setUp();

        $request = $this->buildServerRequest();

        $this->logger = new Log\Test\TestLogger();
        $this->subject = new Src\DataProcessing\ObjecAccessProcessor(
            $this->logger,
            $this->get(Frontend\ContentObject\ContentDataProcessor::class),
        );
        $this->contentObjectRenderer = $this->get(Frontend\ContentObject\ContentObjectRenderer::class);
        $this->contentObjectRenderer->setRequest($request);
        $this->get(Extbase\Configuration\ConfigurationManagerInterface::class)->setRequest($request);
        $this->contentObjectRenderer->start(['uid' => 123], 'tt_content');
    }

    #[Framework\Attributes\Test]
    public function processLogsWarningAndReturnsProcessedDataUnmodifiedIfObjectSourceIsNotConfigured(): void
    {
        self::assertSame([], $this->subject->process($this->contentObjectRenderer, [], [], []));
        self::assertTrue(
            $this->logger->hasWarning([
                'message' => 'Invalid object source configured for "object-access" data processor while processing {table}:{uid}.',
                'context' => [
                    'table' => 'tt_content',
                    'uid' => 123,
                ],
            ]),
        );
    }

    #[Framework\Attributes\Test]
    public function processLogsWarningAndReturnsProcessedDataUnmodifiedIfObjectSourceIsEmpty(): void
    {
        $processorConfiguration = [
            'object' => '',
        ];

        self::assertSame([], $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, []));
        self::assertTrue(
            $this->logger->hasWarning([
                'message' => 'Invalid object source configured for "object-access" data processor while processing {table}:{uid}.',
                'context' => [
                    'table' => 'tt_content',
                    'uid' => 123,
                ],
            ]),
        );
    }

    #[Framework\Attributes\Test]
    public function processLogsWarningAndReturnsProcessedDataUnmodifiedIfPathIsNotConfigured(): void
    {
        $processorConfiguration = [
            'object' => 'someObject',
        ];
        $processedData = [
            'someObject' => new Tests\Functional\Fixtures\Classes\DummyObject('foo'),
        ];

        self::assertSame(
            $processedData,
            $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, $processedData),
        );
        self::assertTrue(
            $this->logger->hasWarning([
                'message' => 'Invalid object or path configured for "object-access" data processor while processing {table}:{uid}.',
                'context' => [
                    'table' => 'tt_content',
                    'uid' => 123,
                ],
            ]),
        );
    }

    #[Framework\Attributes\Test]
    public function processLogsWarningAndReturnsProcessedDataUnmodifiedIfConfiguredObjectCannotBeResolved(): void
    {
        $processorConfiguration = [
            'object' => 'someObject',
            'path' => 'name',
        ];

        self::assertSame([], $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, []));
        self::assertTrue(
            $this->logger->hasWarning([
                'message' => 'Invalid object or path configured for "object-access" data processor while processing {table}:{uid}.',
                'context' => [
                    'table' => 'tt_content',
                    'uid' => 123,
                ],
            ]),
        );
    }

    #[Framework\Attributes\Test]
    public function processAppliesNullToTargetVariableIfConfiguredPathIsNotGettable(): void
    {
        $object = new Tests\Functional\Fixtures\Classes\DummyObject('foo');
        $processorConfiguration = [
            'object' => 'someObject',
            'path' => 'unknownProperty',
        ];
        $processedData = [
            'someObject' => $object,
        ];

        $expected = [
            'someObject' => $object,
            'result' => null,
        ];

        self::assertSame(
            $expected,
            $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, $processedData),
        );
    }

    #[Framework\Attributes\Test]
    public function processResolvesConfiguredPropertyAndAddsItToProcessedDataUsingDefaultKey(): void
    {
        $object = new Tests\Functional\Fixtures\Classes\DummyObject('foo');
        $processorConfiguration = [
            'object' => 'someObject',
            'path' => 'name',
        ];
        $processedData = [
            'someObject' => $object,
        ];

        $expected = [
            'someObject' => $object,
            'result' => 'foo',
        ];

        self::assertSame(
            $expected,
            $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, $processedData),
        );
    }

    #[Framework\Attributes\Test]
    public function processResolvesConfiguredPropertyAndAddsItToProcessedDataUsingConfiguredKey(): void
    {
        $object = new Tests\Functional\Fixtures\Classes\DummyObject('foo');
        $processorConfiguration = [
            'object' => 'someObject',
            'path' => 'name',
            'as' => 'theName',
        ];
        $processedData = [
            'someObject' => $object,
        ];

        $expected = [
            'someObject' => $object,
            'theName' => 'foo',
        ];

        self::assertSame(
            $expected,
            $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, $processedData),
        );
    }

    #[Framework\Attributes\Test]
    public function processAppliesConfiguredDataProcessingToResolvedArrayProperty(): void
    {
        $object = new Tests\Functional\Fixtures\Classes\DummyObject('foo', ['foo.bar' => 'baz']);
        $processorConfiguration = [
            'object' => 'someObject',
            'path' => 'items',
            'dataProcessing.' => [
                '10' => Src\DataProcessing\UnflattenVariableNamesProcessor::class,
            ],
        ];
        $processedData = [
            'someObject' => $object,
        ];

        $expected = [
            'someObject' => $object,
            'result' => [
                'foo' => [
                    'bar' => 'baz',
                ],
            ],
        ];

        self::assertSame(
            $expected,
            $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, $processedData),
        );
    }
}
