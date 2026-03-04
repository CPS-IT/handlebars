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

namespace CPSIT\Typo3Handlebars\Tests\Unit\Renderer\Variables;

use CPSIT\Typo3Handlebars as Src;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * MarkerBasedValueProcessorTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Variables\MarkerBasedValueProcessor::class)]
final class MarkerBasedValueProcessorTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Src\Renderer\Variables\MarkerBasedValueProcessor $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = Src\Renderer\Variables\MarkerBasedValueProcessor::create();
    }

    #[Framework\Attributes\Test]
    public function createThrowsExceptionOnInvalidMarkerPattern(): void
    {
        $markerPattern = 'foo';

        $this->expectExceptionObject(
            new Src\Exception\MarkerPatternIsInvalid($markerPattern),
        );

        Src\Renderer\Variables\MarkerBasedValueProcessor::create($markerPattern);
    }

    #[Framework\Attributes\Test]
    public function createAllowsToDefineCustomMarkerPattern(): void
    {
        $values = [
            '%BAZ%' => [
                'hello' => 'world',
            ],
            '%FOO%' => [
                'foo' => [
                    'baz' => '%BAZ%',
                ],
            ],
            'hello' => [
                'world' => '%FOO%',
            ],
        ];

        $expected = [
            'hello' => [
                'world' => [
                    'foo' => [
                        'baz' => [
                            'hello' => 'world',
                        ],
                    ],
                ],
            ],
        ];

        $subject = Src\Renderer\Variables\MarkerBasedValueProcessor::create('/%(\w+)%/');

        self::assertSame(2, $subject->replaceMarkers($values));
        self::assertSame($expected, $values);
    }

    #[Framework\Attributes\Test]
    public function replaceMarkersRecursivelyDetectsMarkers(): void
    {
        $values = [
            '###BAZ###' => [
                'hello' => 'world',
            ],
            '###FOO###' => [
                'foo' => [
                    'baz' => '###BAZ###',
                ],
            ],
            'hello' => [
                'world' => '###FOO###',
            ],
        ];

        $expected = [
            'hello' => [
                'world' => [
                    'foo' => [
                        'baz' => [
                            'hello' => 'world',
                        ],
                    ],
                ],
            ],
        ];

        self::assertSame(2, $this->subject->replaceMarkers($values));
        self::assertSame($expected, $values);
    }

    #[Framework\Attributes\Test]
    public function replaceMarkersKeepsNonMatchingMarkersByDefault(): void
    {
        $values = [
            '###FOO###' => [
                'foo' => [
                    'baz' => '###BAZ###',
                ],
            ],
            'hello' => [
                'world' => '###FOO###',
            ],
        ];

        $expected = [
            'hello' => [
                'world' => [
                    'foo' => [
                        'baz' => '###BAZ###',
                    ],
                ],
            ],
        ];

        self::assertSame(1, $this->subject->replaceMarkers($values));
        self::assertSame($expected, $values);
    }

    #[Framework\Attributes\Test]
    public function replaceMarkersRemovesNonMatchingMarkersOnIntention(): void
    {
        $values = [
            '###FOO###' => [
                'foo' => [
                    'baz' => '###BAZ###',
                ],
            ],
            'hello' => [
                'world' => '###FOO###',
            ],
        ];

        $expected = [
            'hello' => [
                'world' => [
                    'foo' => [],
                ],
            ],
        ];

        self::assertSame(1, $this->subject->replaceMarkers($values, true));
        self::assertSame($expected, $values);
    }
}
