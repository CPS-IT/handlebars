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

namespace CPSIT\Typo3Handlebars\Cache;

use Composer\InstalledVersions;
use DevTheorem\Handlebars;

/**
 * CacheContext
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class CacheContext
{
    private ?string $cacheIdentifier = null;

    public function __construct(
        public readonly string $template,
        public readonly Handlebars\Options $options = new Handlebars\Options(),
    ) {}

    public function calculateCacheIdentifier(): string
    {
        if ($this->cacheIdentifier === null) {
            $this->cacheIdentifier = sha1(serialize($this->composeCacheIdentifierComponents()));
        }

        return $this->cacheIdentifier;
    }

    /**
     * @return list<mixed>
     */
    private function composeCacheIdentifierComponents(): array
    {
        $components = [
            $this->template,
            $this->options,
        ];

        $packageVersion = $this->lookupHandlebarsPackageVersion();

        if ($packageVersion !== null) {
            $components[] = $packageVersion;
        }

        return $components;
    }

    private function lookupHandlebarsPackageVersion(): ?string
    {
        $version = null;
        $installedPackagesPath = dirname(__DIR__, 2) . '/Resources/Private/Libs/vendor/composer/installed.php';

        if (is_file($installedPackagesPath)) {
            // Classic mode
            /** @var array{versions: array<string, array{version: string}>} $installedPackages */
            $installedPackages = include $installedPackagesPath;
            $version = $installedPackages['versions']['devtheorem/php-handlebars']['version'] ?? null;
        } elseif (class_exists(InstalledVersions::class)) {
            // Composer mode
            try {
                $version = InstalledVersions::getVersion('devtheorem/php-handlebars');
            } catch (\OutOfBoundsException) {
                // Something is wrong if the library is not installed, but this will already
                // be problematic on higher levels. We shouldn't fail in this low level area.
            }
        }

        if (is_string($version)) {
            return $version;
        }

        return null;
    }

    public function __clone(): void
    {
        // Enforce recalculation of the cache identifier on clone
        $this->cacheIdentifier = null;
    }
}
