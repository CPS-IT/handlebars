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

namespace CPSIT\Typo3Handlebars\Tests\Unit\Service;

use CPSIT\Typo3Handlebars as Src;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * AssetTypeTest
 *
 * @author Vladimir Falcon Piva <v.falcon@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Service\AssetType::class)]
final class AssetTypeTest extends TestingFramework\Core\Unit\UnitTestCase
{
    #[Framework\Attributes\Test]
    public function getBooleanAttributesReturnsCorrectAttributesForJavaScript(): void
    {
        $actual = Src\Service\AssetType::JAVASCRIPT->getBooleanAttributes();

        self::assertSame(['async', 'defer', 'nomodule'], $actual);
    }

    #[Framework\Attributes\Test]
    public function getBooleanAttributesReturnsCorrectAttributesForInlineJavaScript(): void
    {
        $actual = Src\Service\AssetType::INLINE_JAVASCRIPT->getBooleanAttributes();

        self::assertSame(['async', 'defer', 'nomodule'], $actual);
    }

    #[Framework\Attributes\Test]
    public function getBooleanAttributesReturnsCorrectAttributesForCss(): void
    {
        $actual = Src\Service\AssetType::CSS->getBooleanAttributes();

        self::assertSame(['disabled'], $actual);
    }

    #[Framework\Attributes\Test]
    public function getBooleanAttributesReturnsCorrectAttributesForInlineCss(): void
    {
        $actual = Src\Service\AssetType::INLINE_CSS->getBooleanAttributes();

        self::assertSame(['disabled'], $actual);
    }

    #[Framework\Attributes\Test]
    public function isInlineReturnsTrueForInlineJavaScript(): void
    {
        $actual = Src\Service\AssetType::INLINE_JAVASCRIPT->isInline();

        self::assertTrue($actual);
    }

    #[Framework\Attributes\Test]
    public function isInlineReturnsTrueForInlineCss(): void
    {
        $actual = Src\Service\AssetType::INLINE_CSS->isInline();

        self::assertTrue($actual);
    }

    #[Framework\Attributes\Test]
    public function isInlineReturnsFalseForJavaScript(): void
    {
        $actual = Src\Service\AssetType::JAVASCRIPT->isInline();

        self::assertFalse($actual);
    }

    #[Framework\Attributes\Test]
    public function isInlineReturnsFalseForCss(): void
    {
        $actual = Src\Service\AssetType::CSS->isInline();

        self::assertFalse($actual);
    }

    #[Framework\Attributes\Test]
    public function isBooleanAttributeReturnsTrueForValidJavaScriptAttributes(): void
    {
        self::assertTrue(Src\Service\AssetType::JAVASCRIPT->isBooleanAttribute('async'));
        self::assertTrue(Src\Service\AssetType::JAVASCRIPT->isBooleanAttribute('defer'));
        self::assertTrue(Src\Service\AssetType::JAVASCRIPT->isBooleanAttribute('nomodule'));
    }

    #[Framework\Attributes\Test]
    public function isBooleanAttributeReturnsTrueForValidCssAttributes(): void
    {
        self::assertTrue(Src\Service\AssetType::CSS->isBooleanAttribute('disabled'));
    }

    #[Framework\Attributes\Test]
    public function isBooleanAttributeReturnsFalseForInvalidAttributes(): void
    {
        self::assertFalse(Src\Service\AssetType::JAVASCRIPT->isBooleanAttribute('disabled'));
        self::assertFalse(Src\Service\AssetType::CSS->isBooleanAttribute('async'));
        self::assertFalse(Src\Service\AssetType::JAVASCRIPT->isBooleanAttribute('crossorigin'));
        self::assertFalse(Src\Service\AssetType::CSS->isBooleanAttribute('media'));
    }

    #[Framework\Attributes\Test]
    public function getCollectorMethodReturnsCorrectMethodForJavaScript(): void
    {
        $actual = Src\Service\AssetType::JAVASCRIPT->getCollectorMethod();

        self::assertSame('addJavaScript', $actual);
    }

    #[Framework\Attributes\Test]
    public function getCollectorMethodReturnsCorrectMethodForInlineJavaScript(): void
    {
        $actual = Src\Service\AssetType::INLINE_JAVASCRIPT->getCollectorMethod();

        self::assertSame('addInlineJavaScript', $actual);
    }

    #[Framework\Attributes\Test]
    public function getCollectorMethodReturnsCorrectMethodForCss(): void
    {
        $actual = Src\Service\AssetType::CSS->getCollectorMethod();

        self::assertSame('addStyleSheet', $actual);
    }

    #[Framework\Attributes\Test]
    public function getCollectorMethodReturnsCorrectMethodForInlineCss(): void
    {
        $actual = Src\Service\AssetType::INLINE_CSS->getCollectorMethod();

        self::assertSame('addInlineStyleSheet', $actual);
    }
}
