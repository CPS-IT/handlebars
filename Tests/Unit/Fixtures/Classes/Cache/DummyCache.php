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

namespace Fr\Typo3Handlebars\Tests\Unit\Fixtures\Classes\Cache;

use Fr\Typo3Handlebars\Cache\CacheInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * DummyCache
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class DummyCache implements CacheInterface
{
    /**
     * @var string
     */
    private $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
    }

    public function get(string $template): ?string
    {
        $cacheFile = $this->resolveCacheFile($template);
        if (file_exists($cacheFile)) {
            return file_get_contents($cacheFile) ?: '';
        }
        return null;
    }

    public function set(string $template, string $compileResult): void
    {
        $cacheFile = $this->resolveCacheFile($template);
        GeneralUtility::mkdir_deep(\dirname($cacheFile));
        file_put_contents($cacheFile, $compileResult);
    }

    private function resolveCacheFile(string $template): string
    {
        return $this->basePath . DIRECTORY_SEPARATOR . sha1($template) . '.php';
    }
}
