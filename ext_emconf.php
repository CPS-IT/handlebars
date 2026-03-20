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

/** @noinspection PhpUndefinedVariableInspection */
$EM_CONF[$_EXTKEY] = [
    'title' => 'Handlebars',
    'description' => 'A TYPO3 extension that provides an entire rendering environment for Handlebars templates. It is seamlessly integrated into TYPO3 and offers extensive configuration options to get all the power out of your templates.',
    'category' => 'fe',
    'version' => '0.8.0',
    'state' => 'beta',
    'author' => 'Elias Häußler',
    'author_email' => 'e.haeussler@familie-redlich.de',
    'author_company' => 'coding. powerful. systems. CPS GmbH',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.11-14.2.99',
            'php' => '8.2.0-8.5.99',
        ],
    ],
];
