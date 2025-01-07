<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2022 Elias Häußler <e.haeussler@familie-redlich.de>
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

use Rector\Config\RectorConfig;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\ValueObject\PhpVersion;
use Ssch\TYPO3Rector\Set\Typo3LevelSetList;
use Ssch\TYPO3Rector\TYPO312\v4\MigrateConfigurationManagerGetContentObjectRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/Classes',
        __DIR__ . '/Configuration',
        __DIR__ . '/Tests',
        __DIR__ . '/ext_*.php',
    ]);

    $rectorConfig->phpVersion(PhpVersion::PHP_82);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_82,
        Typo3LevelSetList::UP_TO_TYPO3_12,
    ]);

    $rectorConfig->skip([
        FirstClassCallableRector::class => [
            __DIR__ . '/Tests/Functional/Renderer/Helper/RenderHelperTest.php',
            __DIR__ . '/Tests/Unit/Renderer/Helper/HelperRegistryTest.php',
        ],

        // @todo Remove once code is rewritten
        MigrateConfigurationManagerGetContentObjectRector::class => [
            __DIR__ . '/Tests/Unit/DataProcessing/AbstractDataProcessorTest.php',
        ],
    ]);
};
