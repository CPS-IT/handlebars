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
 * InvalidHelperException
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class InvalidHelperException extends \Exception
{
    public static function forFunction(string $helperFunction): self
    {
        return new self(
            \sprintf('The helper function "%s" is invalid.', $helperFunction),
            1637339290
        );
    }

    public static function forUnsupportedType(mixed $helperFunction): self
    {
        return new self(
            \sprintf('Only callables, strings and arrays can be defined as helpers, "%s" given.', \get_debug_type($helperFunction)),
            1637339694
        );
    }

    /**
     * @param array{class-string|object, string} $callable
     */
    public static function forInvalidCallable(array $callable): self
    {
        [$className, $methodName] = $callable;

        return new self(
            \sprintf('The helper function with callable [%s, %s] is not valid.', \get_debug_type($className), \get_debug_type($methodName)),
            1638180355
        );
    }
}
