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

namespace Fr\Typo3Handlebars\Cache;

use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Core;

/**
 * HandlebarsCache
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[DependencyInjection\Attribute\AsAlias('handlebars.cache')]
final readonly class HandlebarsCache implements Cache
{
    public function __construct(
        #[DependencyInjection\Attribute\Autowire('@cache.handlebars')]
        private Core\Cache\Frontend\FrontendInterface $cache,
    ) {}

    public function get(string $template): ?string
    {
        $cacheIdentifier = $this->calculateCacheIdentifier($template);
        return $this->cache->get($cacheIdentifier) ?: null;
    }

    public function set(string $template, string $compileResult): void
    {
        $cacheIdentifier = $this->calculateCacheIdentifier($template);
        $this->cache->set($cacheIdentifier, $compileResult);
    }

    protected function calculateCacheIdentifier(string $template): string
    {
        return sha1($template);
    }
}
