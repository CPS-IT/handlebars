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
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * VarDumpHelperTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Helper\VarDumpHelper::class)]
final class VarDumpHelperTest extends TestingFramework\Core\Unit\UnitTestCase
{
    use Tests\HandlebarsTemplateTestTrait;

    private Src\Renderer\Helper\VarDumpHelper $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\Renderer\Helper\VarDumpHelper();
    }

    #[Framework\Attributes\Test]
    public function helperCanBeUsedInTemplate(): void
    {
        Core\Utility\DebugUtility::useAnsiColor(false);

        $expected = <<<EOF
Debug
array (1 item)
   foo => "baz" (3 chars)
EOF;

        self::assertRenderedTemplateEqualsString(
            '{{varDump}}',
            $expected,
            [
                'foo' => 'baz',
            ],
            [
                'varDump' => $this->subject->render(...),
            ],
        );

        Core\Utility\DebugUtility::useAnsiColor(true);
    }

    #[Framework\Attributes\Test]
    public function renderReturnsDumpedContext(): void
    {
        Core\Utility\DebugUtility::useAnsiColor(false);

        $renderingContext = [
            'foo' => 'baz',
        ];
        $data = [];

        $context = new Handlebars\HelperOptions(
            'foo',
            [],
            static fn() => '',
            static fn() => '',
            0,
            $renderingContext,
            $data,
        );

        $expected = <<<EOF
Debug
array (1 item)
   foo => "baz" (3 chars)
EOF;

        self::assertEquals(
            new Handlebars\SafeString($expected),
            $this->subject->render($context),
        );

        Core\Utility\DebugUtility::useAnsiColor(true);
    }

    #[Framework\Attributes\Test]
    public function renderAllowsToDefineDebugTitle(): void
    {
        Core\Utility\DebugUtility::useAnsiColor(false);

        $renderingContext = [
            'foo' => 'baz',
        ];
        $data = [];

        $context = new Handlebars\HelperOptions(
            'foo',
            [
                'title' => 'foo',
            ],
            static fn() => '',
            static fn() => '',
            0,
            $renderingContext,
            $data,
        );

        $expected = <<<EOF
foo
array (1 item)
   foo => "baz" (3 chars)
EOF;

        self::assertEquals(
            new Handlebars\SafeString($expected),
            $this->subject->render($context),
        );

        Core\Utility\DebugUtility::useAnsiColor(true);
    }
}
