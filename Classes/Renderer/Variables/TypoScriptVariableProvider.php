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

namespace Fr\Typo3Handlebars\Renderer\Variables;

use Fr\Typo3Handlebars\Configuration;
use TYPO3\CMS\Extbase;

/**
 * TypoScriptVariableProvider
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class TypoScriptVariableProvider implements VariableProvider
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $variables = null;

    public function __construct(
        private readonly Extbase\Configuration\ConfigurationManagerInterface $configurationManager,
    ) {}

    public function get(): array
    {
        if ($this->variables === null) {
            $this->variables = $this->fetchVariables();
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
        throw new \LogicException('Variables cannot be modified.', 1736274326);
    }

    public function offsetUnset(mixed $offset): never
    {
        throw new \LogicException('Variables cannot be modified.', 1736274336);
    }

    public static function getPriority(): int
    {
        return 50;
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchVariables(): array
    {
        $typoScriptConfiguration = $this->configurationManager->getConfiguration(
            Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
            Configuration\Extension::NAME,
        );

        return $typoScriptConfiguration['variables'] ?? [];
    }
}
