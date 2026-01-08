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

namespace CPSIT\Typo3Handlebars\Exception;

/**
 * InvalidAssetConfigurationException
 *
 * Thrown when asset configuration is invalid or incomplete.
 *
 * @author Vladimir Falcon Piva <v.falcon@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class InvalidAssetConfigurationException extends Exception
{
    public static function forMissingSource(string $identifier, string $type): self
    {
        return new self(
            \sprintf(
                'Asset configuration "%s" (type: %s) is missing required "source" parameter.',
                $identifier,
                $type,
            ),
            1704800001,
        );
    }

    public static function forInvalidIdentifier(string $type): self
    {
        return new self(
            \sprintf(
                'Asset configuration (type: %s) has invalid or empty identifier.',
                $type,
            ),
            1704800002,
        );
    }

    public static function forInvalidConfiguration(string $identifier, string $type): self
    {
        return new self(
            \sprintf(
                'Asset configuration "%s" (type: %s) must be an array.',
                $identifier,
                $type,
            ),
            1704800003,
        );
    }

    public static function forUnknownAssetType(string $type): self
    {
        return new self(
            \sprintf(
                'Unknown asset type "%s". Valid types are: javaScript, inlineJavaScript, css, inlineCss.',
                $type,
            ),
            1704800004,
        );
    }

    public static function forInvalidAssetsArray(string $type): self
    {
        return new self(
            \sprintf(
                'Assets configuration for type "%s" must be an array.',
                $type,
            ),
            1704800005,
        );
    }
}
