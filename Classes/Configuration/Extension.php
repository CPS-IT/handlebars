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

namespace Fr\Typo3Handlebars\Configuration;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Extension
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @codeCoverageIgnore
 */
final class Extension
{
    public const KEY = 'handlebars';
    public const NAME = 'Handlebars';

    /**
     * Register additional caches.
     *
     * FOR USE IN ext_localconf.php ONLY.
     */
    public static function registerCaches(): void
    {
        if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['handlebars'] ?? null)) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['handlebars'] = [];
        }
        if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['handlebars']['groups'])) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['handlebars']['groups'] = ['pages'];
        }
    }

    /**
     * Load additional libraries provided by PHAR file (only to be used in non-Composer-mode).
     *
     * FOR USE IN ext_localconf.php AND NON-COMPOSER-MODE ONLY.
     */
    public static function loadVendorLibraries(): void
    {
        // Vendor libraries are already available in Composer mode
        if (Environment::isComposerMode()) {
            return;
        }

        $vendorPharFile = GeneralUtility::getFileAbsFileName('EXT:handlebars/Resources/Private/Libs/vendors.phar');
        if (file_exists($vendorPharFile)) {
            require_once 'phar://' . $vendorPharFile . '/vendor/autoload.php';
        }
    }
}
