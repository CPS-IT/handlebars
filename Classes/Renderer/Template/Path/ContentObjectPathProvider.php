<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2025 Elias Häußler <e.haeussler@familie-redlich.de>
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

namespace Fr\Typo3Handlebars\Renderer\Template\Path;

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
     * @var list<array{
     *     partialRootPaths: RootPathStackItem,
     *     templateRootPaths: RootPathStackItem,
     * }>
     */
    private array $stack = [];

    private ?int $currentItem = null;

    /**
     * @param array<string, mixed> $configuration
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

    public function getPartialRootPaths(): array
    {
        return $this->getMergedRootPathsFromStack('partialRootPaths');
    }

    public function getTemplateRootPaths(): array
    {
        return $this->getMergedRootPathsFromStack('templateRootPaths');
    }

    public function isCacheable(): bool
    {
        // Caching is done internally, based on the current stack
        return false;
    }

    /**
     * @param 'partialRootPaths'|'templateRootPaths' $type
     * @return array<int, string>
     */
    private function getMergedRootPathsFromStack(string $type): array
    {
        if ($this->currentItem === null) {
            return [];
        }

        // Merge and cache root paths
        if ($this->stack[$this->currentItem][$type]['merged'] === null) {
            $this->stack[$this->currentItem][$type]['merged'] = $this->stack[0][$type]['current'];

            for ($i = 1; $i < $this->currentItem; $i++) {
                Core\Utility\ArrayUtility::mergeRecursiveWithOverrule(
                    $this->stack[$this->currentItem][$type]['merged'],
                    $this->stack[$i][$type]['current'],
                );
            }
        }

        /* @phpstan-ignore return.type */
        return $this->stack[$this->currentItem][$type]['merged'];
    }

    public static function getPriority(): int
    {
        return 100;
    }
}
