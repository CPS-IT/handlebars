<?php

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

defined('TYPO3') or die();

use TYPO3\CMS\Core;

// Configure handlebars cache
if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['handlebars'] ?? null)) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['handlebars'] = [];
}
if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['handlebars']['groups'] ?? null)) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['handlebars']['groups'] = ['pages'];
}

// Configure handlebars_media cache
$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['handlebars_media'] = [
    'backend' => Core\Cache\Backend\SimpleFileBackend::class,
    'frontend' => Core\Cache\Frontend\PhpFrontend::class,
    'groups' => ['pages'],
];
