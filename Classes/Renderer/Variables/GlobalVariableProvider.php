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

/**
 * GlobalVariableProvider
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final readonly class GlobalVariableProvider implements VariableProvider
{
    /**
     * @param array<string, mixed> $variables
     */
    public function __construct(
        #[DependencyInjection\Attribute\Autowire('%handlebars.variables%')]
        private array $variables,
    ) {}

    public function get(): array
    {
        return $this->variables;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->variables[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->variables[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): never
    {
        throw new \LogicException('Variables cannot be modified.', 1736274549);
    }

    public function offsetUnset(mixed $offset): never
    {
        throw new \LogicException('Variables cannot be modified.', 1736274551);
    }

    public static function getPriority(): int
    {
        return 0;
    }
}
