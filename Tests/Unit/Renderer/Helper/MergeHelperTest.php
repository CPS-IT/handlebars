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

namespace CPSIT\Typo3Handlebars\Tests\Unit\Renderer\Helper;

use CPSIT\Typo3Handlebars as Src;
use CPSIT\Typo3Handlebars\Tests;
use DevTheorem\Handlebars;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * MergeHelperTest
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Helper\MergeHelper::class)]
final class MergeHelperTest extends TestingFramework\Core\Unit\UnitTestCase
{
    use Tests\HandlebarsTemplateTestTrait;

    private Src\Renderer\Helper\MergeHelper $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\Renderer\Helper\MergeHelper();
    }

    #[Framework\Attributes\Test]
    public function helperCanBeUsedInTemplate(): void
    {
        self::assertRenderedTemplateEqualsString(
            '{{{jsonEncode (merge foo baz)}}}',
            '{"foo":"baz","baz":"foo"}',
            [
                'foo' => [
                    'foo' => 'baz',
                ],
                'baz' => [
                    'baz' => 'foo',
                ],
            ],
            [
                'jsonEncode' => static fn($_, array $context) => json_encode($context),
                'merge' => $this->subject->render(...),
            ],
        );
    }

    #[Framework\Attributes\Test]
    public function renderReturnsMergedContext(): void
    {
        $hash = [
            'bar' => 'foobaz',
        ];
        $scope = [];
        $data = [];
        $fn = static fn() => '';
        $options = new Handlebars\HelperOptions('merge', $hash, $fn, $fn, 0, $scope, $data);

        $contexts = [
            [
                'foo' => 'baz',
            ],
            [
                'baz' => 'foo',
            ],
        ];

        $expected = [
            'foo' => 'baz',
            'baz' => 'foo',
            'bar' => 'foobaz',
        ];

        self::assertSame($expected, $this->subject->render($options, ...$contexts));
    }
}
