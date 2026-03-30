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
 * JoinHelperTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Helper\JoinHelper::class)]
final class JoinHelperTest extends TestingFramework\Core\Unit\UnitTestCase
{
    use Tests\HandlebarsTemplateTestTrait;

    private Src\Renderer\Helper\JoinHelper $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\Renderer\Helper\JoinHelper();
    }

    #[Framework\Attributes\Test]
    public function helperCanBeUsedInTemplate(): void
    {
        self::assertRenderedTemplateEqualsString(
            '{{join "foo" baz}}',
            'foobaz',
            [
                'baz' => new class () implements \Stringable {
                    public function __toString(): string
                    {
                        return 'baz';
                    }
                },
            ],
            [
                'join' => $this->subject->render(...),
            ],
        );
    }

    #[Framework\Attributes\Test]
    public function renderReturnsImplodedString(): void
    {
        $scope = [];
        $data = [];
        $fn = static fn() => '';
        $options = new Handlebars\HelperOptions(
            $scope,
            $data,
            new Handlebars\RuntimeContext(),
            'join',
            [],
            0,
            $fn,
            $fn,
        );

        $parts = [
            'foo',
            new class () implements \Stringable {
                public function __toString(): string
                {
                    return 'baz';
                }
            },
        ];

        self::assertSame('foobaz', $this->subject->render($options, ...$parts));
    }
}
