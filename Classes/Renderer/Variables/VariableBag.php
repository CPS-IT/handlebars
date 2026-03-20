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

namespace CPSIT\Typo3Handlebars\Renderer\Variables;

use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * VariableBag
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @implements \ArrayAccess<string, mixed>
 */
final class VariableBag implements \ArrayAccess
{
    /**
     * @var array<string|int, mixed>|null
     */
    private ?array $variables = null;

    /**
     * @param iterable<VariableProvider> $providers
     */
    public function __construct(
        #[DependencyInjection\Attribute\AutowireIterator('handlebars.variable_provider', defaultPriorityMethod: 'getPriority')]
        private readonly iterable $providers,
    ) {}

    /**
     * @return array<string|int, mixed>
     */
    public function get(): array
    {
        if ($this->variables === null) {
            $this->variables = $this->fetchVariablesFromProviders();
        }

        return $this->variables;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->get()[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get()[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): never
    {
        throw new \LogicException('Variables cannot be modified.', 1736274871);
    }

    public function offsetUnset(mixed $offset): never
    {
        throw new \LogicException('Variables cannot be modified.', 1736274873);
    }

    /**
     * @return array<string|int, mixed>
     */
    private function fetchVariablesFromProviders(): array
    {
        $providerVariables = [];
        $mergedVariables = [];

        foreach ($this->providers as $provider) {
            array_unshift($providerVariables, $provider->get());
        }

        foreach ($providerVariables as $variables) {
            ArrayUtility::mergeRecursiveWithOverrule($mergedVariables, $variables);
        }

        return $mergedVariables;
    }
}
