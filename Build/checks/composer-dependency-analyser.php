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

use PHPUnit\Framework;
use ShipMonk\ComposerDependencyAnalyser;

$rootPath = dirname(__DIR__, 2);
$configuration = new ComposerDependencyAnalyser\Config\Configuration();
$configuration
    ->addPathToScan($rootPath . '/Classes', false)
    ->addPathToScan($rootPath . '/Configuration', false)
    ->addPathToScan($rootPath . '/Tests', true)
    ->ignoreUnknownClasses([
        Framework\Attributes\AllowMockObjectsWithoutExpectations::class,
    ])
;

return $configuration;
