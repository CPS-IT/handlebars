<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2020 Elias Häußler <e.haeussler@familie-redlich.de>
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

namespace Fr\Typo3Handlebars\DependencyInjection;

use Fr\Typo3Handlebars\Renderer;
use Symfony\Component\DependencyInjection;

/**
 * HandlebarsHelperPass
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 * @codeCoverageIgnore
 */
final readonly class HandlebarsHelperPass implements DependencyInjection\Compiler\CompilerPassInterface
{
    public function __construct(
        private string $helperTagName,
    ) {}

    public function process(DependencyInjection\ContainerBuilder $container): void
    {
        $registryDefinition = $container->getDefinition(Renderer\Helper\HelperRegistry::class);

        // Register tagged Handlebars helper at all Helper-aware renderers
        foreach ($container->findTaggedServiceIds($this->helperTagName) as $serviceId => $tags) {
            foreach (array_filter($tags) as $attributes) {
                $this->validateTag($serviceId, $attributes);

                $registryDefinition->addMethodCall(
                    'add',
                    [
                        $attributes['identifier'],
                        [new DependencyInjection\Reference($serviceId), $attributes['method']],
                    ],
                );
            }
        }
    }

    /**
     * @param array<string, string> $tagAttributes
     */
    private function validateTag(string $serviceId, array $tagAttributes): void
    {
        if (!\array_key_exists('identifier', $tagAttributes) || (string)$tagAttributes['identifier'] === '') {
            throw new \InvalidArgumentException(
                \sprintf('Service tag "%s" requires an identifier attribute to be defined, missing in: %s', $this->helperTagName, $serviceId),
                1606236820,
            );
        }
        if (!\array_key_exists('method', $tagAttributes) || (string)$tagAttributes['method'] === '') {
            throw new \InvalidArgumentException(
                \sprintf('Service tag "%s" requires an method attribute to be defined, missing in: %s', $this->helperTagName, $serviceId),
                1606245140,
            );
        }
    }
}
