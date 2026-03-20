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
 * ResolveMarkersProcessorTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\DataProcessing\ResolveMarkersProcessor::class)]
final class ResolveMarkersProcessorTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\FrontendRequestTrait;

    private Src\DataProcessing\ResolveMarkersProcessor $subject;
    private Frontend\ContentObject\ContentObjectRenderer $contentObjectRenderer;

    public function setUp(): void
    {
        parent::setUp();

        $request = $this->buildServerRequest();

        $this->subject = new Src\DataProcessing\ResolveMarkersProcessor();
        $this->contentObjectRenderer = $this->get(Frontend\ContentObject\ContentObjectRenderer::class);
        $this->contentObjectRenderer->setRequest($request);
        $this->get(Extbase\Configuration\ConfigurationManagerInterface::class)->setRequest($request);
    }

    #[Framework\Attributes\Test]
    public function processAllowsOverwritingMarkerPattern(): void
    {
        $processorConfiguration = [
            'pattern' => '/MARKER: (\w+)/',
        ];
        $processedData = [
            'MARKER: foo' => 'baz',
            'foo' => 'MARKER: foo',
        ];

        $expected = [
            'foo' => 'baz',
        ];

        self::assertSame($expected, $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, $processedData));
    }

    #[Framework\Attributes\Test]
    public function processDoesNotRemoveNonMatchingMarkersByDefault(): void
    {
        $processedData = [
            '###BAZ###' => 'baz',
            'foo' => '###FOO###',
        ];

        $expected = [
            '###BAZ###' => 'baz',
            'foo' => '###FOO###',
        ];

        self::assertSame($expected, $this->subject->process($this->contentObjectRenderer, [], [], $processedData));
    }

    #[Framework\Attributes\Test]
    public function processAllowsToRemoveNonMatchingMarkers(): void
    {
        $processorConfiguration = [
            'removeNonMatchingMarkers' => '1',
        ];
        $processedData = [
            '###BAZ###' => 'baz',
            'foo' => '###FOO###',
        ];

        $expected = [
            '###BAZ###' => 'baz',
        ];

        self::assertSame($expected, $this->subject->process($this->contentObjectRenderer, [], $processorConfiguration, $processedData));
    }

    #[Framework\Attributes\Test]
    public function processReturnsProcessedDataWithResolvedMarkers(): void
    {
        $processedData = [
            '###BAZ###' => 'bar',
            '###FOO###' => [
                'baz' => '###BAZ###',
            ],
            'foo' => '###FOO###',
        ];

        $expected = [
            'foo' => [
                'baz' => 'bar',
            ],
        ];

        self::assertSame($expected, $this->subject->process($this->contentObjectRenderer, [], [], $processedData));
    }
}
