<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2021 Elias Häußler <e.haeussler@familie-redlich.de>
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

namespace Fr\Typo3Handlebars\DependencyInjection\Extension;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * HandlebarsExtension configuration structure.
 *
 * Defines the following configuration structure for the {@see HandlebarsExtension}:
 *
 * - defaultData:
 *   - [data]: any default data passed to the renderer
 *
 * - template:
 *   - [template_root_paths]: numeric array of template root paths
 *   - [partial_root_paths]: numeric array of partial root paths
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 * @codeCoverageIgnore
 */
final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('handlebars');
        $rootNode = $treeBuilder->getRootNode();

        /** @phpstan-ignore-next-line */
        $rootNode
            ->children()
                ->arrayNode('default_data')
                    ->performNoDeepMerging()
                    ->variablePrototype()->end()
                ->end()
                ->arrayNode('template')
                    ->children()
                        ->arrayNode('template_root_paths')
                            ->beforeNormalization()
                                ->always($this->getRootPathNormalizationClosure())
                            ->end()
                            ->performNoDeepMerging()
                            ->variablePrototype()->end()
                        ->end()
                        ->arrayNode('partial_root_paths')
                            ->beforeNormalization()
                                ->always($this->getRootPathNormalizationClosure())
                            ->end()
                            ->performNoDeepMerging()
                            ->variablePrototype()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    private function getRootPathNormalizationClosure(): \Closure
    {
        return function ($transitions) {
            if ($transitions === null) {
                return [];
            }
            if (!\is_array($transitions)) {
                throw new \InvalidArgumentException(
                    \sprintf('Illegal value for root path configuration. Only numeric arrays are allowed, got "%s" instead.', \gettype($transitions)),
                    1615835938
                );
            }
            if ($this->containsNonNumericIndexes($transitions)) {
                throw new \InvalidArgumentException(
                    'Found non-numeric indexes for root path configuration. Only numeric arrays are allowed!',
                    1615836050
                );
            }
            return $transitions;
        };
    }

    /**
     * @param array<mixed, mixed> $array
     */
    private function containsNonNumericIndexes(array $array): bool
    {
        return \count(array_filter(array_keys($array), 'is_string')) !== 0;
    }
}
