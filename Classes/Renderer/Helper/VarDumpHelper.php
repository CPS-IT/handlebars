<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2020 Elias Häußler <e.haeussler@familie-redlich.de>
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

namespace Fr\Typo3Handlebars\Renderer\Helper;

use Fr\Typo3Handlebars\Attribute;
use TYPO3\CMS\Core;

/**
 * VarDumpHelper
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final readonly class VarDumpHelper implements HelperInterface
{
    /**
     * @param array<string|int, mixed> $context
     */
    #[Attribute\AsHelper('varDump')]
    public static function evaluate(array $context): string
    {
        \ob_start();

        Core\Utility\DebugUtility::debug($context['_this']);

        return (string)\ob_get_clean();
    }
}
