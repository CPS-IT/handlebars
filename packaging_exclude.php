<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2021 Elias Häußler <e.haeussler@familie-redlich.de>
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

return [
    'directories' => [
        '.build',
        '.git',
        '.github',
        'resources\\/private\\/libs\\/build',
        'tailor-version-upload',
        'tests',
    ],
    'files' => [
        'DS_Store',
        'codecov.yml',
        'composer.lock',
        'dependency-checker.json',
        'docker-compose.yml',
        'editorconfig',
        'gitattributes',
        'gitignore',
        'packaging_exclude.php',
        'php-cs-fixer.php',
        'phpstan.neon',
        'phpstan-baseline.neon',
        'phpunit.xml',
        'rector.php',
        'renovate.json',
        'typoscript-lint.yml',
    ],
];
