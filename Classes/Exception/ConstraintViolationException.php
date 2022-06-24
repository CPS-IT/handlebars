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
 * ConstraintViolationException
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class ConstraintViolationException extends \Exception
{
    public static function createForUniqueHelper(string $name): self
    {
        return new self(
            sprintf('The Handlebars helper "%s" is already registered.', $name),
            1622135017
        );
    }

    public static function createForUnsupportedExtension(string $extensionKey): self
    {
        return new self(
            sprintf('The given extension with key "%s" is not valid or not available.', $extensionKey),
            1622135154
        );
    }
}
