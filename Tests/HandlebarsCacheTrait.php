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

namespace CPSIT\Typo3Handlebars\Tests;

use CPSIT\Typo3Handlebars as Src;
use CPSIT\Typo3Handlebars\Tests\Unit\Fixtures;
use TYPO3\CMS\Core;

/**
 * HandlebarsCacheTrait
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
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
