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

namespace CPSIT\Typo3Handlebars\Frontend\Assets;

/**
 * AssetType
 *
 * Enum representing the different types of assets that can be registered
 * with TYPO3's AssetCollector. Each type knows its boolean attributes and
 * corresponding AssetCollector method.
 *
 * @author Vladimir Falcon Piva <v.falcon@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
enum AssetType: string
{
    case Css = 'css';
    case InlineCss = 'inlineCss';
    case InlineJavaScript = 'inlineJavaScript';
    case JavaScript = 'javaScript';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return \array_map(
            static fn(self $assetType) => $assetType->value,
            self::cases(),
        );
    }

    /**
     * Get boolean HTML attributes for this asset type.
     *
     * @return list<string>
     */
    public function getBooleanAttributes(): array
    {
        return match ($this) {
            self::JavaScript, self::InlineJavaScript => ['async', 'defer', 'nomodule'],
            self::Css, self::InlineCss => ['disabled'],
        };
    }

    /**
     * Check if an attribute is a boolean attribute for this asset type.
     */
    public function isBooleanAttribute(string $name): bool
    {
        return \in_array($name, $this->getBooleanAttributes(), true);
    }

    /**
     * Check if this is an inline asset type.
     */
    public function isInline(): bool
    {
        return match ($this) {
            self::InlineJavaScript, self::InlineCss => true,
            self::JavaScript, self::Css => false,
        };
    }
}
