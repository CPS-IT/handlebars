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

namespace Fr\Typo3Handlebars\Renderer\Template;

use Symfony\Component\DependencyInjection;

/**
 * TemplatePaths
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class TemplatePaths
{
    /**
     * @var array<int, string>|null
     */
    private ?array $partialRootPaths = null;

    /**
     * @var array<int, string>|null
     */
    private ?array $templateRootPaths = null;

    /**
     * @param iterable<Path\PathProvider> $pathProviders
     */
    public function __construct(
        #[DependencyInjection\Attribute\AutowireIterator('handlebars.template_path_provider', defaultPriorityMethod: 'getPriority')]
        private readonly iterable $pathProviders,
    ) {}

    /**
     * @return array<int, string>
     */
    public function getPartialRootPaths(): array
    {
        return $this->resolvePaths(
            static fn(Path\PathProvider $pathProvider) => $pathProvider->getPartialRootPaths(),
            $this->partialRootPaths,
        );
    }

    /**
     * @return array<int, string>
     */
    public function getTemplateRootPaths(): array
    {
        return $this->resolvePaths(
            static fn(Path\PathProvider $pathProvider) => $pathProvider->getTemplateRootPaths(),
            $this->templateRootPaths,
        );
    }

    /**
     * @param callable(Path\PathProvider): array<int, string> $mapFunction
     * @param array<int, string>|null $rootPaths
     * @return array<int, string>
     */
    private function resolvePaths(callable $mapFunction, ?array &$rootPaths): array
    {
        // Early return if root paths are already resolved and cached
        if ($rootPaths !== null) {
            return $rootPaths;
        }

        $cacheable = true;
        $paths = [];

        // Resolve root paths from path providers
        foreach ($this->pathProviders as $pathProvider) {
            \array_unshift($paths, $mapFunction($pathProvider));

            if (!$pathProvider->isCacheable()) {
                $cacheable = false;
            }
        }

        // Merge and sort all root paths
        $mergedPaths = array_replace(...$paths);
        ksort($mergedPaths);

        // Cache root paths if possible
        if ($cacheable) {
            $rootPaths = $mergedPaths;
        }

        return $mergedPaths;
    }
}
