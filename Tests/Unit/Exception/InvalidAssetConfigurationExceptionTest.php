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

namespace CPSIT\Typo3Handlebars\Tests\Unit\Exception;

use CPSIT\Typo3Handlebars as Src;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * InvalidAssetConfigurationExceptionTest
 *
 * @author Vladimir Falcon Piva <v.falcon@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Exception\InvalidAssetConfigurationException::class)]
final class InvalidAssetConfigurationExceptionTest extends TestingFramework\Core\Unit\UnitTestCase
{
    #[Framework\Attributes\Test]
    public function forMissingSourceReturnsExceptionWithCorrectMessage(): void
    {
        $actual = Src\Exception\InvalidAssetConfigurationException::forMissingSource('my-asset', 'javaScript');

        self::assertSame(
            'Asset configuration "my-asset" (type: javaScript) is missing required "source" parameter.',
            $actual->getMessage()
        );
        self::assertSame(1704800001, $actual->getCode());
    }

    #[Framework\Attributes\Test]
    public function forInvalidIdentifierReturnsExceptionWithCorrectMessage(): void
    {
        $actual = Src\Exception\InvalidAssetConfigurationException::forInvalidIdentifier('css');

        self::assertSame(
            'Asset configuration (type: css) has invalid or empty identifier.',
            $actual->getMessage()
        );
        self::assertSame(1704800002, $actual->getCode());
    }

    #[Framework\Attributes\Test]
    public function forInvalidConfigurationReturnsExceptionWithCorrectMessage(): void
    {
        $actual = Src\Exception\InvalidAssetConfigurationException::forInvalidConfiguration('test-asset', 'inlineJavaScript');

        self::assertSame(
            'Asset configuration "test-asset" (type: inlineJavaScript) must be an array.',
            $actual->getMessage()
        );
        self::assertSame(1704800003, $actual->getCode());
    }

    #[Framework\Attributes\Test]
    public function forUnknownAssetTypeReturnsExceptionWithCorrectMessage(): void
    {
        $actual = Src\Exception\InvalidAssetConfigurationException::forUnknownAssetType('invalidType');

        self::assertSame(
            'Unknown asset type "invalidType". Valid types are: javaScript, inlineJavaScript, css, inlineCss.',
            $actual->getMessage()
        );
        self::assertSame(1704800004, $actual->getCode());
    }

    #[Framework\Attributes\Test]
    public function forInvalidAssetsArrayReturnsExceptionWithCorrectMessage(): void
    {
        $actual = Src\Exception\InvalidAssetConfigurationException::forInvalidAssetsArray('javaScript');

        self::assertSame(
            'Assets configuration for type "javaScript" must be an array.',
            $actual->getMessage()
        );
        self::assertSame(1704800005, $actual->getCode());
    }
}
