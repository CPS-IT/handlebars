<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2020 Elias Häußler <e.haeussler@familie-redlich.de>
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

namespace Fr\Typo3Handlebars\Tests\Unit;

use Fr\Typo3Handlebars as Src;
use TYPO3\CMS\Core;

/**
 * HandlebarsCacheTrait
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
trait HandlebarsCacheTrait
{
    private ?Fixtures\Classes\Cache\DummyCache $cache = null;

    protected function getCache(): Src\Cache\Cache
    {
        if ($this->cache === null) {
            $this->cache = new Fixtures\Classes\Cache\DummyCache($this->getCachePath());
        }
        return $this->cache;
    }

    protected function clearCache(): bool
    {
        if (file_exists($this->getCachePath())) {
            return Core\Utility\GeneralUtility::rmdir($this->getCachePath(), true);
        }
        return true;
    }

    protected function getCachePath(): string
    {
        return Core\Core\Environment::getVarPath() . DIRECTORY_SEPARATOR . 'handlebarsCache';
    }
}
