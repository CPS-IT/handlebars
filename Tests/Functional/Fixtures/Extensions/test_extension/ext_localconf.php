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

use Fr\Typo3Handlebars\TestExtension\Controller;
use TYPO3\CMS\Extbase;

defined('TYPO3') or die();

Extbase\Utility\ExtensionUtility::configurePlugin(
    'TestExtension',
    'TestDefaultTemplate',
    [
        Controller\TestController::class => 'defaultTemplate',
    ],
);

Extbase\Utility\ExtensionUtility::configurePlugin(
    'TestExtension',
    'TestRenderedTemplate',
    [
        Controller\TestController::class => 'renderedTemplate',
    ],
);

Extbase\Utility\ExtensionUtility::configurePlugin(
    'TestExtension',
    'TestSpecificTemplate',
    [
        Controller\TestController::class => 'specificTemplate',
    ],
);
