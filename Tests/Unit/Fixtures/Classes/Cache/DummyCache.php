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

namespace CPSIT\Typo3Handlebars\Tests\Unit\Fixtures\Classes\Cache;

use CPSIT\Typo3Handlebars\Cache;
use TYPO3\CMS\Core;

/**
 * DummyCache
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 */
final readonly class DummyCache implements Cache\Cache
{
    private string $basePath;

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
        Core\Utility\GeneralUtility::mkdir_deep(\dirname($cacheFile));
        file_put_contents($cacheFile, $compileResult);
    }

    private function resolveCacheFile(string $template): string
    {
        return $this->basePath . DIRECTORY_SEPARATOR . sha1($template) . '.php';
    }
}
