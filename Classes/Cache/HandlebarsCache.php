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
