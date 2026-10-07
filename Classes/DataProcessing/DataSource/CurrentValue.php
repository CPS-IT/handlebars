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

namespace CPSIT\Typo3Handlebars\DataProcessing\DataSource;

use CPSIT\Typo3Handlebars\Utility;

/**
 * Wrapper for values used as current value of a content object.
 *
 * stdWrap functions (e.g. "current = 1") expect the current value to be a string
 * and fail for values which cannot be converted to strings, such as objects. This
 * wrapper provides a safe string representation, while the original value remains
 * accessible. Arrays are represented as comma-separated list of their stringable
 * values (except null), all other values which cannot be converted to strings are
 * represented as empty string. Stringable values (scalar values, null, and stringable
 * objects) are not wrapped.
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final readonly class CurrentValue implements \Stringable
{
    public function __construct(
        public mixed $value,
    ) {}

    public static function wrap(mixed $value): self|int|float|string|bool|\Stringable|null
    {
        // Stringable values don't need to be wrapped
        if (Utility\StringUtility::isStringable($value)) {
            return $value;
        }

        return new self($value);
    }

    public function __toString(): string
    {
        if (Utility\StringUtility::isStringable($this->value)) {
            return (string)$this->value;
        }

        if (is_array($this->value)) {
            return implode(
                ',',
                array_filter(
                    $this->value,
                    static fn(mixed $item) => $item !== null && Utility\StringUtility::isStringable($item),
                ),
            );
        }

        return '';
    }
}
