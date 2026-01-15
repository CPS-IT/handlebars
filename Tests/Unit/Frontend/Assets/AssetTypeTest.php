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

namespace CPSIT\Typo3Handlebars\Tests\Unit\Frontend\Assets;

use CPSIT\Typo3Handlebars as Src;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * AssetTypeTest
 *
 * @author Vladimir Falcon Piva <v.falcon@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Frontend\Assets\AssetType::class)]
final class AssetTypeTest extends TestingFramework\Core\Unit\UnitTestCase
{
    #[Framework\Attributes\Test]
    public function getBooleanAttributesReturnsCorrectAttributesForJavaScript(): void
    {
        $actual = Src\Frontend\Assets\AssetType::JavaScript->getBooleanAttributes();

        self::assertSame(['async', 'defer', 'nomodule'], $actual);
    }

    #[Framework\Attributes\Test]
    public function getBooleanAttributesReturnsCorrectAttributesForInlineJavaScript(): void
    {
        $actual = Src\Frontend\Assets\AssetType::InlineJavaScript->getBooleanAttributes();

        self::assertSame(['async', 'defer', 'nomodule'], $actual);
    }

    #[Framework\Attributes\Test]
    public function getBooleanAttributesReturnsCorrectAttributesForCss(): void
    {
        $actual = Src\Frontend\Assets\AssetType::Css->getBooleanAttributes();

        self::assertSame(['disabled'], $actual);
    }

    #[Framework\Attributes\Test]
    public function getBooleanAttributesReturnsCorrectAttributesForInlineCss(): void
    {
        $actual = Src\Frontend\Assets\AssetType::InlineCss->getBooleanAttributes();

        self::assertSame(['disabled'], $actual);
    }

    #[Framework\Attributes\Test]
    public function isInlineReturnsTrueForInlineJavaScript(): void
    {
        $actual = Src\Frontend\Assets\AssetType::InlineJavaScript->isInline();

        self::assertTrue($actual);
    }

    #[Framework\Attributes\Test]
    public function isInlineReturnsTrueForInlineCss(): void
    {
        $actual = Src\Frontend\Assets\AssetType::InlineCss->isInline();

        self::assertTrue($actual);
    }

    #[Framework\Attributes\Test]
    public function isInlineReturnsFalseForJavaScript(): void
    {
        $actual = Src\Frontend\Assets\AssetType::JavaScript->isInline();

        self::assertFalse($actual);
    }

    #[Framework\Attributes\Test]
    public function isInlineReturnsFalseForCss(): void
    {
        $actual = Src\Frontend\Assets\AssetType::Css->isInline();

        self::assertFalse($actual);
    }

    #[Framework\Attributes\Test]
    public function isBooleanAttributeReturnsTrueForValidJavaScriptAttributes(): void
    {
        self::assertTrue(Src\Frontend\Assets\AssetType::JavaScript->isBooleanAttribute('async'));
        self::assertTrue(Src\Frontend\Assets\AssetType::JavaScript->isBooleanAttribute('defer'));
        self::assertTrue(Src\Frontend\Assets\AssetType::JavaScript->isBooleanAttribute('nomodule'));
    }

    #[Framework\Attributes\Test]
    public function isBooleanAttributeReturnsTrueForValidCssAttributes(): void
    {
        self::assertTrue(Src\Frontend\Assets\AssetType::Css->isBooleanAttribute('disabled'));
    }

    #[Framework\Attributes\Test]
    public function isBooleanAttributeReturnsFalseForInvalidAttributes(): void
    {
        self::assertFalse(Src\Frontend\Assets\AssetType::JavaScript->isBooleanAttribute('disabled'));
        self::assertFalse(Src\Frontend\Assets\AssetType::Css->isBooleanAttribute('async'));
        self::assertFalse(Src\Frontend\Assets\AssetType::JavaScript->isBooleanAttribute('crossorigin'));
        self::assertFalse(Src\Frontend\Assets\AssetType::Css->isBooleanAttribute('media'));
    }
}
