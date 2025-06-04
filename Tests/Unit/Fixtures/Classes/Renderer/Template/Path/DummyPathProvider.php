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

namespace Fr\Typo3Handlebars\Tests\Unit\Fixtures\Classes\Renderer\Template\Path;

use Fr\Typo3Handlebars\Renderer;

/**
 * DummyPathProvider
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 */
final class DummyPathProvider implements Renderer\Template\Path\PathProvider
{
    /**
     * @param array<int, string> $templateRootPaths
     * @param array<int, string> $partialRootPaths
     */
    public function __construct(
        public array $templateRootPaths = [],
        public array $partialRootPaths = [],
        public bool $cacheable = true,
    ) {}

    public function getPartialRootPaths(): array
    {
        return $this->partialRootPaths;
    }

    public function getTemplateRootPaths(): array
    {
        return $this->templateRootPaths;
    }

    public function isCacheable(): bool
    {
        return $this->cacheable;
    }

    public static function getPriority(): int
    {
        return 10;
    }
}
