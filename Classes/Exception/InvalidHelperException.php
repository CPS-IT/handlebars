<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2021 Elias Häußler <e.haeussler@familie-redlich.de>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace Fr\Typo3Handlebars\Exception;

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
            sprintf('The helper function "%s" is invalid.', $helperFunction),
            1637339290
        );
    }

    /**
     * @param mixed $helperFunction
     * @return self
     */
    public static function forUnsupportedType($helperFunction): self
    {
        return new self(
            sprintf('Only callables, strings and arrays can be defined as helpers, "%s" given.', gettype($helperFunction)),
            1637339694
        );
    }

    /**
     * @param array{class-string|object, string} $callable
     * @return self
     */
    public static function forInvalidCallable(array $callable): self
    {
        [$className, $methodName] = $callable;

        return new self(
            sprintf('The helper function with callable [%s, %s] is not valid.', gettype($className), gettype($methodName)),
            1638180355
        );
    }
}
