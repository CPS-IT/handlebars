<?php

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
