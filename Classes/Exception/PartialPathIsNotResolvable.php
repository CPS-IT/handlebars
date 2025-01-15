<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2025 Elias Häußler <e.haeussler@familie-redlich.de>
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
 * PartialPathIsNotResolvable
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class PartialPathIsNotResolvable extends Exception
{
    public function __construct(string $path, ?string $format = null)
    {
        if ($format !== null) {
            $formatMessage = sprintf(' with format "%s"', $format);
        } else {
            $formatMessage = '';
        }

        parent::__construct(
            \sprintf('The partial path "%s"%s cannot be resolved.', $path, $formatMessage),
            1736254715,
        );
    }
}
