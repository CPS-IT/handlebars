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

namespace CPSIT\Typo3Handlebars\Renderer\Template\Path;

use TYPO3\CMS\Core;

/**
 * ContentObjectPathProvider
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 *
 * @phpstan-type RootPathStackItem array{
 *     current: array<int, string>,
 *     merged: array<int, string>|null,
 * }
 */
final class ContentObjectPathProvider implements PathProvider, Core\SingletonInterface
{
    /**
     * @var list<array<self::*, RootPathStackItem>>
     */
    private array $stack = [];

    private ?int $currentItem = null;

    /**
     * @param array{templateRootPath?: string, partialRootPath?: string, templateRootPaths?: array<int, string>, partialRootPaths?: array<int, string>} $configuration
     */
    public function push(array $configuration): void
    {
        $partialRootPaths = [];
        $templateRootPaths = [];

        if (is_array($configuration['templateRootPaths'] ?? null)) {
            $templateRootPaths = $configuration['templateRootPaths'];
        }
        if (is_array($configuration['partialRootPaths'] ?? null)) {
            $partialRootPaths = $configuration['partialRootPaths'];
        }

        // A single root path receives highest priority
        if (is_string($configuration['templateRootPath'] ?? null)) {
            $templateRootPaths[PHP_INT_MAX] = $configuration['templateRootPath'];
        }
        if (is_string($configuration['partialRootPath'] ?? null)) {
            $partialRootPaths[PHP_INT_MAX] = $configuration['partialRootPath'];
        }

        ksort($templateRootPaths);
        ksort($partialRootPaths);

        $this->stack[] = [
            'partialRootPaths' => [
                'current' => $partialRootPaths,
                'merged' => null,
            ],
            'templateRootPaths' => [
                'current' => $templateRootPaths,
                'merged' => null,
            ],
        ];

        if ($this->currentItem === null) {
            $this->currentItem = 0;
        } else {
            $this->currentItem++;
        }
    }

    public function pop(): void
    {
        if ($this->stack === []) {
            return;
        }

        array_pop($this->stack);

        if ($this->stack === []) {
            $this->currentItem = null;
        } else {
            $this->currentItem--;
        }
    }

    public function isEmpty(): bool
    {
        return $this->currentItem === null;
    }

    public function getPartialRootPaths(): array
    {
        return $this->getMergedRootPathsFromStack(self::PARTIALS);
    }

    public function getTemplateRootPaths(): array
    {
        return $this->getMergedRootPathsFromStack(self::TEMPLATES);
    }

    public function isCacheable(): bool
    {
        // Caching is done internally, based on the current stack
        return false;
    }

    /**
     * @param self::* $type
     * @return array<int, string>
     */
    private function getMergedRootPathsFromStack(string $type): array
    {
        if ($this->currentItem === null) {
            return [];
        }

        // Merge and cache root paths
        if ($this->stack[$this->currentItem][$type]['merged'] === null) {
            $merged = $this->stack[0][$type]['current'];

            for ($i = 1; $i <= $this->currentItem; $i++) {
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $merged = array_replace($merged, $this->stack[$i][$type]['current']);
            }

            ksort($merged);

            $this->stack[$this->currentItem][$type]['merged'] = $merged;
        }

        return $this->stack[$this->currentItem][$type]['merged'];
    }

    public static function getPriority(): int
    {
        return 100;
    }
}
