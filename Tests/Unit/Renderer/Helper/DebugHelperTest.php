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
use PHPUnit\Framework;
use TYPO3\CMS\Extbase;
use TYPO3\TestingFramework;

/**
 * DebugHelperTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Helper\DebugHelper::class)]
final class DebugHelperTest extends TestingFramework\Core\Unit\UnitTestCase
{
    use Tests\HandlebarsTemplateTestTrait;

    private Src\Renderer\Helper\DebugHelper $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\Renderer\Helper\DebugHelper();

        // Pre-render var_dump, because the first call contains stylesheet, whereas following calls don't
        $this->renderVarDump(null);
    }

    #[Framework\Attributes\Test]
    public function helperDumpsCurrentScopeIfSubjectIsOmitted(): void
    {
        $expected = $this->renderVarDump(['foo' => 'baz']);

        self::assertRenderedTemplateEqualsString(
            '{{debug}}',
            $expected,
            [
                'foo' => 'baz',
            ],
            [
                'debug' => $this->subject->render(...),
            ],
        );
    }

    #[Framework\Attributes\Test]
    public function helperDumpsGivenSubject(): void
    {
        $expected = $this->renderVarDump('baz');

        self::assertRenderedTemplateEqualsString(
            '{{debug foo}}',
            $expected,
            [
                'foo' => 'baz',
            ],
            [
                'debug' => $this->subject->render(...),
            ],
        );
    }

    #[Framework\Attributes\Test]
    public function helperRespectsGivenTitle(): void
    {
        $expected = $this->renderVarDump('baz', 'Foo');

        self::assertRenderedTemplateEqualsString(
            '{{debug foo title="Foo"}}',
            $expected,
            [
                'foo' => 'baz',
            ],
            [
                'debug' => $this->subject->render(...),
            ],
        );
    }

    #[Framework\Attributes\Test]
    public function helperRespectsGivenMaxDepth(): void
    {
        $context = [
            'foo' => [
                'baz' => 'boo',
            ],
        ];

        $expected = $this->renderVarDump($context, maxDepth: 1);

        self::assertRenderedTemplateEqualsString(
            '{{debug maxDepth=1}}',
            $expected,
            $context,
            [
                'debug' => $this->subject->render(...),
            ],
        );
    }

    private function renderVarDump(mixed $subject, string $title = 'Debug', int $maxDepth = 12): string
    {
        return Extbase\Utility\DebuggerUtility::var_dump(
            $subject,
            $title,
            $maxDepth,
            false,
            false,
            true,
        );
    }
}
