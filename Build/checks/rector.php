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

use Rector\CodingStyle\Rector\FuncCall\FunctionFirstClassCallableRector;
use Rector\Config\RectorConfig;
use Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;
use Rector\Php81\Rector\Array_\ArrayToFirstClassCallableRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\ValueObject\PhpVersion;
use Ssch\TYPO3Rector\Set\Typo3LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rootPath = dirname(__DIR__, 2);

    $rectorConfig->paths([
        $rootPath . '/Classes',
        $rootPath . '/Configuration',
        $rootPath . '/Tests',
        $rootPath . '/ext_*.php',
    ]);

    $rectorConfig->phpVersion(PhpVersion::PHP_82);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_82,
        Typo3LevelSetList::UP_TO_TYPO3_13,
    ]);

    $rectorConfig->skip([
        ArrayToFirstClassCallableRector::class => [
            $rootPath . '/Tests/Unit/Renderer/Helper/HelperRegistryTest.php',
        ],
        FunctionFirstClassCallableRector::class => [
            $rootPath . '/Tests/Unit/Renderer/Helper/HelperRegistryTest.php',
        ],
        // @todo Remove once support for TYPO3 v13 is dropped
        RemoveExtraParametersRector::class => [
            $rootPath . '/Tests/Functional/Frontend/ContentObject/HandlebarsTemplateContentObjectAssetTest.php',
            $rootPath . '/Tests/Functional/Frontend/ContentObject/HandlebarsTemplateContentObjectTest.php',
        ],
    ]);
};
