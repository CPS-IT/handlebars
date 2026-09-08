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
use PHPUnit\Framework;
use TYPO3\CMS\Frontend;
use TYPO3\TestingFramework;

/**
 * DataSourceCollectionTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\DataProcessing\DataSource\DataSourceCollection::class)]
final class DataSourceCollectionTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    protected bool $initializeDatabase = false;

    private Src\DataProcessing\DataSource\DataSourceCollection $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\DataProcessing\DataSource\DataSourceCollection();
    }

    #[Framework\Attributes\Test]
    public function forCreatesCollectionWithAllGivenDataSources(): void
    {
        $cObj = $this->get(Frontend\ContentObject\ContentObjectRenderer::class);
        $cObj->data = ['uid' => 1];

        $collection = Src\DataProcessing\DataSource\DataSourceCollection::for(
            $cObj,
            ['foo' => 'COC-BAZ'],
            ['foo' => 'PC-BAZ'],
            ['foo' => 'PD-BAZ'],
        );

        self::assertSame(
            ['uid' => 1],
            $collection->get(Src\DataProcessing\DataSource\DataSource::ContentObjectRenderer),
        );
        self::assertSame(
            ['foo' => 'COC-BAZ'],
            $collection->get(Src\DataProcessing\DataSource\DataSource::ContentObjectConfiguration),
        );
        self::assertSame(
            ['foo' => 'PC-BAZ'],
            $collection->get(Src\DataProcessing\DataSource\DataSource::ProcessorConfiguration),
        );
        self::assertSame(
            ['foo' => 'PD-BAZ'],
            $collection->get(Src\DataProcessing\DataSource\DataSource::ProcessedData),
        );
    }

    #[Framework\Attributes\Test]
    public function forOnlySetsExplicitlyGivenDataSources(): void
    {
        $collection = Src\DataProcessing\DataSource\DataSourceCollection::for(
            processedData: ['foo' => 'PD-BAZ'],
        );

        self::assertTrue($collection->has(Src\DataProcessing\DataSource\DataSource::ProcessedData));
        self::assertFalse($collection->has(Src\DataProcessing\DataSource\DataSource::ContentObjectRenderer));
        self::assertFalse($collection->has(Src\DataProcessing\DataSource\DataSource::ContentObjectConfiguration));
        self::assertFalse($collection->has(Src\DataProcessing\DataSource\DataSource::ProcessorConfiguration));
    }

    #[Framework\Attributes\Test]
    public function getAddsGivenDataSourceToCollection(): void
    {
        $this->subject->set(
            Src\DataProcessing\DataSource\DataSource::ProcessedData,
            ['foo' => 'baz'],
        );

        self::assertTrue($this->subject->has(Src\DataProcessing\DataSource\DataSource::ProcessedData));
        self::assertSame(['foo' => 'baz'], $this->subject->get(Src\DataProcessing\DataSource\DataSource::ProcessedData));
    }

    #[Framework\Attributes\Test]
    public function getReturnsEmptyArrayIfDataSourceIsNotPresent(): void
    {
        self::assertSame([], $this->subject->get(Src\DataProcessing\DataSource\DataSource::ProcessedData));
    }

    #[Framework\Attributes\Test]
    public function removeDataSourceRemovesGivenDataSourceFromCollection(): void
    {
        $this->subject->set(
            Src\DataProcessing\DataSource\DataSource::ProcessedData,
            ['foo' => 'baz'],
        );

        $this->subject->remove(Src\DataProcessing\DataSource\DataSource::ProcessedData);

        self::assertFalse($this->subject->has(Src\DataProcessing\DataSource\DataSource::ProcessedData));
        self::assertSame([], $this->subject->get(Src\DataProcessing\DataSource\DataSource::ProcessedData));
    }

    #[Framework\Attributes\Test]
    public function resolveWalksThroughConfiguredDataSourcesByPriorityIfNoDataSourceIsGiven(): void
    {
        $this->subject->set(
            Src\DataProcessing\DataSource\DataSource::ProcessedData,
            ['foo' => 'PD-BAZ'],
        );
        $this->subject->set(
            Src\DataProcessing\DataSource\DataSource::ContentObjectConfiguration,
            ['foo' => 'COC-BAZ'],
        );

        self::assertSame('PD-BAZ', $this->subject->resolve('foo'));
    }

    #[Framework\Attributes\Test]
    public function resolveWalksThroughGivenDataSourcesInGivenOrder(): void
    {
        $this->subject->set(
            Src\DataProcessing\DataSource\DataSource::ProcessedData,
            ['foo' => 'PD-BAZ'],
        );
        $this->subject->set(
            Src\DataProcessing\DataSource\DataSource::ContentObjectConfiguration,
            ['foo' => 'COC-BAZ'],
        );

        self::assertSame(
            'COC-BAZ',
            $this->subject->resolve(
                'foo',
                [
                    Src\DataProcessing\DataSource\DataSource::ContentObjectConfiguration,
                    Src\DataProcessing\DataSource\DataSource::ProcessedData,
                ],
            ),
        );
    }

    #[Framework\Attributes\Test]
    public function resolveReturnsResolvedValueForGivenDataSource(): void
    {
        $this->subject->set(
            Src\DataProcessing\DataSource\DataSource::ProcessedData,
            ['foo' => 'PD-BAZ'],
        );
        $this->subject->set(
            Src\DataProcessing\DataSource\DataSource::ContentObjectConfiguration,
            ['foo' => 'COC-BAZ'],
        );

        self::assertSame(
            'PD-BAZ',
            $this->subject->resolve(
                'foo',
                Src\DataProcessing\DataSource\DataSource::ProcessedData,
            ),
        );
    }

    #[Framework\Attributes\Test]
    public function resolveReturnsDefaultValueIfKeyCannotBeFound(): void
    {
        $this->subject->set(
            Src\DataProcessing\DataSource\DataSource::ProcessedData,
            ['foo' => 'PD-BAZ'],
        );
        $this->subject->set(
            Src\DataProcessing\DataSource\DataSource::ContentObjectConfiguration,
            ['foo' => 'COC-BAZ'],
        );

        self::assertSame(
            'baz',
            $this->subject->resolve(
                'foo',
                Src\DataProcessing\DataSource\DataSource::ContentObjectRenderer,
                'baz',
            ),
        );
    }

    #[Framework\Attributes\Test]
    public function resolveSupportsSlashSeparatedPathToReachNestedKeys(): void
    {
        $this->subject->set(
            Src\DataProcessing\DataSource\DataSource::ProcessedData,
            [
                'nested' => [
                    'deep' => [
                        'value' => 'bar',
                    ],
                ],
            ],
        );

        self::assertSame(
            'bar',
            $this->subject->resolve(
                'nested/deep/value',
                Src\DataProcessing\DataSource\DataSource::ProcessedData,
            ),
        );
    }

    #[Framework\Attributes\Test]
    public function resolveReturnsDefaultValueIfNestedPathSegmentIsMissing(): void
    {
        $this->subject->set(
            Src\DataProcessing\DataSource\DataSource::ProcessedData,
            [
                'nested' => [
                    'deep' => [
                        'value' => 'bar',
                    ],
                ],
            ],
        );

        self::assertSame(
            'baz',
            $this->subject->resolve(
                'nested/missing',
                Src\DataProcessing\DataSource\DataSource::ProcessedData,
                'baz',
            ),
        );
    }

    #[Framework\Attributes\Test]
    public function resolveTreatsKeyWithTrailingDotAsLiteralKeyRatherThanAsPath(): void
    {
        // TypoScript arrays store sub-properties of e.g. "dataProcessing { ... }" under a single
        // flat key with a literal trailing dot ("dataProcessing."), not as a nested "dataProcessing"
        // path segment. Resolving such a key must keep matching it literally.
        $this->subject->set(
            Src\DataProcessing\DataSource\DataSource::ProcessorConfiguration,
            [
                'dataProcessing.' => [
                    '10' => 'foo',
                ],
            ],
        );

        self::assertSame(
            ['10' => 'foo'],
            $this->subject->resolve(
                'dataProcessing.',
                Src\DataProcessing\DataSource\DataSource::ProcessorConfiguration,
            ),
        );
    }

    #[Framework\Attributes\Test]
    public function resolveThrowsExceptionIfKeyCannotBeFoundAndNotOptionalAndNoDefaultIsGiven(): void
    {
        $this->subject->set(Src\DataProcessing\DataSource\DataSource::ProcessedData, []);

        $this->expectExceptionObject(
            new Src\Exception\PathIsMissingInDataSource(
                'foo',
                Src\DataProcessing\DataSource\DataSource::ProcessedData,
            ),
        );

        $this->subject->resolve(
            'foo',
            Src\DataProcessing\DataSource\DataSource::ProcessedData,
            optional: false,
        );
    }

    #[Framework\Attributes\Test]
    public function resolveReturnsDefaultValueInsteadOfThrowingIfDefaultIsGivenEvenWhenNotOptional(): void
    {
        $this->subject->set(Src\DataProcessing\DataSource\DataSource::ProcessedData, []);

        self::assertSame(
            'baz',
            $this->subject->resolve(
                'foo',
                Src\DataProcessing\DataSource\DataSource::ProcessedData,
                'baz',
                optional: false,
            ),
        );
    }

    #[Framework\Attributes\Test]
    public function resolveDoesNotThrowByDefaultIfKeyCannotBeFound(): void
    {
        $this->subject->set(Src\DataProcessing\DataSource\DataSource::ProcessedData, []);

        self::assertNull(
            $this->subject->resolve('foo', Src\DataProcessing\DataSource\DataSource::ProcessedData),
        );
    }

    #[Framework\Attributes\Test]
    public function resolveReturnsValueFromLaterDataSourceInsteadOfThrowingIfNotOptional(): void
    {
        $this->subject->set(Src\DataProcessing\DataSource\DataSource::ProcessedData, []);
        $this->subject->set(
            Src\DataProcessing\DataSource\DataSource::ContentObjectConfiguration,
            ['foo' => 'COC-BAZ'],
        );

        self::assertSame(
            'COC-BAZ',
            $this->subject->resolve(
                'foo',
                [
                    Src\DataProcessing\DataSource\DataSource::ProcessedData,
                    Src\DataProcessing\DataSource\DataSource::ContentObjectConfiguration,
                ],
                optional: false,
            ),
        );
    }

    #[Framework\Attributes\Test]
    public function resolveThrowsExceptionReferencingLastAttemptedDataSourceIfNoneMatchAndNotOptional(): void
    {
        $this->subject->set(Src\DataProcessing\DataSource\DataSource::ProcessedData, []);
        $this->subject->set(Src\DataProcessing\DataSource\DataSource::ContentObjectConfiguration, []);

        $this->expectExceptionObject(
            new Src\Exception\PathIsMissingInDataSource(
                'foo',
                Src\DataProcessing\DataSource\DataSource::ContentObjectConfiguration,
            ),
        );

        $this->subject->resolve(
            'foo',
            [
                Src\DataProcessing\DataSource\DataSource::ProcessedData,
                Src\DataProcessing\DataSource\DataSource::ContentObjectConfiguration,
            ],
            optional: false,
        );
    }

    #[Framework\Attributes\Test]
    public function resolveDoesNotThrowIfNotOptionalButNoDataSourcesAreConfiguredAtAll(): void
    {
        self::assertNull($this->subject->resolve('foo', optional: false));
    }

    #[Framework\Attributes\Test]
    public function withAppliesGivenValueToAllDataSources(): void
    {
        $this->subject->set(
            Src\DataProcessing\DataSource\DataSource::ProcessedData,
            ['foo' => 'PD-BAZ'],
        );
        $this->subject->set(
            Src\DataProcessing\DataSource\DataSource::ContentObjectConfiguration,
            ['foo' => 'COC-BAZ'],
        );

        $this->subject->with('foo', 'baz');

        self::assertSame(
            'baz',
            $this->subject->resolve('foo', Src\DataProcessing\DataSource\DataSource::ProcessedData),
        );
        self::assertSame(
            'baz',
            $this->subject->resolve('foo', Src\DataProcessing\DataSource\DataSource::ContentObjectConfiguration),
        );
    }

    #[Framework\Attributes\Test]
    public function withAppliesGivenValueToGivenDataSource(): void
    {
        $this->subject->set(
            Src\DataProcessing\DataSource\DataSource::ProcessedData,
            ['foo' => 'PD-BAZ'],
        );
        $this->subject->set(
            Src\DataProcessing\DataSource\DataSource::ContentObjectConfiguration,
            ['foo' => 'COC-BAZ'],
        );

        $this->subject->with('foo', 'baz', Src\DataProcessing\DataSource\DataSource::ProcessedData);

        self::assertSame(
            'baz',
            $this->subject->resolve('foo', Src\DataProcessing\DataSource\DataSource::ProcessedData),
        );
        self::assertSame(
            'COC-BAZ',
            $this->subject->resolve('foo', Src\DataProcessing\DataSource\DataSource::ContentObjectConfiguration),
        );
    }

    #[Framework\Attributes\Test]
    public function withAppliesGivenValueToGivenDataSources(): void
    {
        $this->subject->set(
            Src\DataProcessing\DataSource\DataSource::ProcessedData,
            ['foo' => 'PD-BAZ'],
        );
        $this->subject->set(
            Src\DataProcessing\DataSource\DataSource::ContentObjectConfiguration,
            ['foo' => 'COC-BAZ'],
        );

        $this->subject->with(
            'foo',
            'baz',
            [
                Src\DataProcessing\DataSource\DataSource::ProcessedData,
                Src\DataProcessing\DataSource\DataSource::ContentObjectRenderer,
            ],
        );

        self::assertSame(
            'baz',
            $this->subject->resolve('foo', Src\DataProcessing\DataSource\DataSource::ProcessedData),
        );
        self::assertSame(
            'baz',
            $this->subject->resolve('foo', Src\DataProcessing\DataSource\DataSource::ContentObjectRenderer),
        );
        self::assertSame(
            'COC-BAZ',
            $this->subject->resolve('foo', Src\DataProcessing\DataSource\DataSource::ContentObjectConfiguration),
        );
    }
}
