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

namespace CPSIT\Typo3Handlebars\Tests\Functional\DataProcessing\DataSource;

use CPSIT\Typo3Handlebars as Src;
use CPSIT\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Frontend;
use TYPO3\TestingFramework;

/**
 * DataSourceProviderTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\DataProcessing\DataSource\DataSourceProvider::class)]
final class DataSourceProviderTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\FrontendRequestTrait;

    private Src\DataProcessing\DataSource\DataSourceProvider $subject;
    private Frontend\ContentObject\ContentObjectRenderer $contentObjectRenderer;

    public function setUp(): void
    {
        parent::setUp();

        $request = $this->buildServerRequest();

        $this->subject = new Src\DataProcessing\DataSource\DataSourceProvider(
            new Core\TypoScript\TypoScriptService(),
        );
        $this->contentObjectRenderer = new Frontend\ContentObject\ContentObjectRenderer();
        $this->contentObjectRenderer->setRequest($request);
        $this->get(Extbase\Configuration\ConfigurationManagerInterface::class)->setRequest($request);
    }

    #[Framework\Attributes\Test]
    public function provideReturnsNullIfNoVariablesAreConfigured(): void
    {
        $collection = new Src\DataProcessing\DataSource\DataSourceCollection();

        self::assertNull($this->subject->provide($collection));
    }

    #[Framework\Attributes\Test]
    public function provideReturnsConfiguredDataIfNoDataSourcesAreDefined(): void
    {
        $collection = new Src\DataProcessing\DataSource\DataSourceCollection();
        $collection->set(
            Src\DataProcessing\DataSource\DataSource::ProcessorConfiguration,
            [
                'data.' => [
                    'foo' => 'baz',
                ],
                'variables.' => [
                    'foo' => 'TEXT',
                    'foo.' => [
                        'field' => 'foo',
                    ],
                ],
            ],
        );

        $expected = [
            'foo' => 'baz',
        ];

        self::assertSame($expected, $this->subject->provide($collection));
    }

    #[Framework\Attributes\Test]
    public function provideReturnsDataFromProcessedDataIfNoDataSourcesAreDefined(): void
    {
        $collection = new Src\DataProcessing\DataSource\DataSourceCollection();
        $collection->set(
            Src\DataProcessing\DataSource\DataSource::ProcessedData,
            [
                'data' => [
                    'foo' => 'baz',
                ],
            ],
        );
        $collection->set(
            Src\DataProcessing\DataSource\DataSource::ProcessorConfiguration,
            [
                'variables.' => [
                    'foo' => 'TEXT',
                    'foo.' => [
                        'field' => 'foo',
                    ],
                ],
            ],
        );

        $expected = [
            'foo' => 'baz',
        ];

        self::assertSame($expected, $this->subject->provide($collection));
    }

    /**
     * @return \Generator<string, array{\CPSIT\Typo3Handlebars\DataProcessing\DataSource\DataSource, array<string, mixed>}>
     */
    public static function provideAcceptsSingleDataSourceDataProvider(): \Generator
    {
        yield 'content object configuration' => [
            Src\DataProcessing\DataSource\DataSource::ContentObjectConfiguration,
            ['foo' => 'COC-BAZ'],
        ];
        yield 'content object renderer' => [
            Src\DataProcessing\DataSource\DataSource::ContentObjectRenderer,
            ['foo' => 'COR-BAZ'],
        ];
        yield 'processed data' => [
            Src\DataProcessing\DataSource\DataSource::ProcessedData,
            ['foo' => 'PD-BAZ'],
        ];
    }

    /**
     * @param array<string, mixed> $expected
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('provideAcceptsSingleDataSourceDataProvider')]
    public function provideAcceptsSingleDataSource(
        Src\DataProcessing\DataSource\DataSource $dataSource,
        array $expected,
    ): void {
        $collection = new Src\DataProcessing\DataSource\DataSourceCollection();
        $collection->set(
            Src\DataProcessing\DataSource\DataSource::ContentObjectRenderer,
            ['foo' => 'COR-BAZ'],
        );
        $collection->set(
            Src\DataProcessing\DataSource\DataSource::ContentObjectConfiguration,
            ['foo' => 'COC-BAZ'],
        );
        $collection->set(
            Src\DataProcessing\DataSource\DataSource::ProcessedData,
            ['foo' => 'PD-BAZ'],
        );
        $collection->set(
            Src\DataProcessing\DataSource\DataSource::ProcessorConfiguration,
            [
                'dataSource' => $dataSource->value,
                'variables.' => [
                    'foo' => 'TEXT',
                    'foo.' => [
                        'field' => 'foo',
                    ],
                ],
            ],
        );

        self::assertSame($expected, $this->subject->provide($collection));
    }

    /**
     * @return \Generator<string, array{array<int, string>, array<string, mixed>|null}>
     */
    public static function provideAcceptsDifferentDataSourcesDataProvider(): \Generator
    {
        yield 'no data sources' => [
            [],
            null,
        ];
        yield 'single data source' => [
            [Src\DataProcessing\DataSource\DataSource::ContentObjectRenderer->value],
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
     * @param array<string, mixed>|null $expected
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('provideAcceptsDifferentDataSourcesDataProvider')]
    public function provideAcceptsDifferentDataSources(array $dataSources, ?array $expected): void
    {
        $collection = new Src\DataProcessing\DataSource\DataSourceCollection();
        $collection->set(
            Src\DataProcessing\DataSource\DataSource::ContentObjectRenderer,
            ['foo' => 'COR-BAZ'],
        );
        $collection->set(
            Src\DataProcessing\DataSource\DataSource::ContentObjectConfiguration,
            [
                'bar.' => [
                    'foo' => 'COC-BAZ',
                ],
            ],
        );
        $collection->set(
            Src\DataProcessing\DataSource\DataSource::ProcessedData,
            ['foo' => 'PD-BAZ'],
        );
        $collection->set(
            Src\DataProcessing\DataSource\DataSource::ProcessorConfiguration,
            [
                'dataSource.' => $dataSources,
                'variables.' => [
                    'foo' => 'TEXT',
                    'foo.' => [
                        'field' => 'foo',
                    ],
                ],
            ],
        );

        self::assertSame($expected, $this->subject->provide($collection));
    }

    #[Framework\Attributes\Test]
    public function provideThrowsExceptionOnInvalidDataSourceKeyword(): void
    {
        $collection = new Src\DataProcessing\DataSource\DataSourceCollection();
        $collection->set(
            Src\DataProcessing\DataSource\DataSource::ProcessorConfiguration,
            [
                'dataSource' => 'foo',
            ],
        );

        $this->expectExceptionObject(
            new Src\Exception\DataSourceIsNotSupported('foo'),
        );

        $this->subject->provide($collection);
    }

    #[Framework\Attributes\Test]
    public function provideThrowsExceptionOnMissingDataSource(): void
    {
        $collection = new Src\DataProcessing\DataSource\DataSourceCollection();
        $collection->set(
            Src\DataProcessing\DataSource\DataSource::ProcessorConfiguration,
            [
                'dataSource' => Src\DataProcessing\DataSource\DataSource::ContentObjectRenderer->value,
            ],
        );

        $this->expectExceptionObject(
            new Src\Exception\DataSourceIsMissingInCollection(Src\DataProcessing\DataSource\DataSource::ContentObjectRenderer),
        );

        $this->subject->provide($collection);
    }

    #[Framework\Attributes\Test]
    public function provideHandlesDataSourcesWithConfiguredPath(): void
    {
        $collection = new Src\DataProcessing\DataSource\DataSourceCollection();
        $collection->set(
            Src\DataProcessing\DataSource\DataSource::ProcessedData,
            [
                'foo' => [
                    'baz' => [
                        'bar' => 'foo',
                    ],
                ],
            ],
        );
        $collection->set(
            Src\DataProcessing\DataSource\DataSource::ProcessorConfiguration,
            [
                'dataSource' => 'processedData:foo.baz',
                'variables.' => [
                    'bar' => 'TEXT',
                    'bar.' => [
                        'field' => 'bar',
                    ],
                ],
            ],
        );

        $expected = [
            'bar' => 'foo',
        ];

        self::assertSame($expected, $this->subject->provide($collection));
    }

    #[Framework\Attributes\Test]
    public function provideThrowsExceptionOnInvalidDataSourcePath(): void
    {
        $collection = new Src\DataProcessing\DataSource\DataSourceCollection();
        $collection->set(
            Src\DataProcessing\DataSource\DataSource::ProcessedData,
            [
                'foo' => [
                    'baz' => [
                        'bar' => 'foo',
                    ],
                ],
            ],
        );
        $collection->set(
            Src\DataProcessing\DataSource\DataSource::ProcessorConfiguration,
            [
                'dataSource' => 'processedData:foo.bar',
                'variables.' => [
                    'bar' => 'TEXT',
                    'bar.' => [
                        'field' => 'bar',
                    ],
                ],
            ],
        );

        $this->expectExceptionObject(
            new Src\Exception\PathIsMissingInDataSource('foo.bar', Src\DataProcessing\DataSource\DataSource::ProcessedData),
        );

        $this->subject->provide($collection);
    }
}
