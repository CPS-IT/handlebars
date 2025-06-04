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

namespace Fr\Typo3Handlebars\DependencyInjection\Extension;

use Symfony\Component\Config;

/**
 * HandlebarsExtension configuration structure.
 *
 * Defines the following configuration structure for the {@see HandlebarsExtension}:
 *
 * - variables:
 *   - [data]: any default variable passed to the renderer
 *
 * - view:
 *   - [templateRootPaths]: numeric array of template root paths
 *   - [partialRootPaths]: numeric array of partial root paths
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 * @codeCoverageIgnore
 */
final readonly class Configuration implements Config\Definition\ConfigurationInterface
{
    public function getConfigTreeBuilder(): Config\Definition\Builder\TreeBuilder
    {
        $treeBuilder = new Config\Definition\Builder\TreeBuilder('handlebars');

        /* @phpstan-ignore method.notFound */
        $treeBuilder
            ->getRootNode()
            ->children()
                ->arrayNode('variables')
                    ->performNoDeepMerging()
                    ->variablePrototype()->end()
                ->end()
                ->arrayNode('view')
                    ->children()
                        ->arrayNode('templateRootPaths')
                            ->beforeNormalization()
                                ->always($this->getRootPathNormalizationClosure())
                            ->end()
                            ->performNoDeepMerging()
                            ->variablePrototype()->end()
                        ->end()
                        ->arrayNode('partialRootPaths')
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
                    \sprintf('Illegal value for root path configuration. Only numeric arrays are allowed, got "%s" instead.', \get_debug_type($transitions)),
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
     * @param array<string|int, mixed> $array
     */
    private function containsNonNumericIndexes(array $array): bool
    {
        return \count(array_filter(array_keys($array), 'is_string')) !== 0;
    }
}
