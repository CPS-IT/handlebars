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
        'documentation\\/build',
        'tailor-version-upload',
        'tests',
    ],
    'files' => [
        'DS_Store',
        'composer.lock',
        'editorconfig',
        'gitattributes',
        'gitignore',
        'mkdocs.yml',
        'packaging_exclude.php',
        'php_cs',
        'phpstan.neon',
        'phpunit.ci.xml',
        'phpunit.xml',
        'sonar-project.properties',
        'typoscript-lint.yml',
    ],
];
