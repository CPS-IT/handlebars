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

use Fr\Typo3Handlebars\Renderer\HelperAwareInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * HandlebarsHelperPass
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @codeCoverageIgnore
 */
final class HandlebarsHelperPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private $helperTagName;

    /**
     * @var string
     */
    private $rendererTagName;

    /**
     * @var Definition[]
     */
    private $rendererDefinitions = [];

    public function __construct(string $helperTagName, string $rendererTagName)
    {
        $this->helperTagName = $helperTagName;
        $this->rendererTagName = $rendererTagName;
    }

    public function process(ContainerBuilder $container): void
    {
        $this->fetchRendererDefinitions($container);

        // Register tagged Handlebars helper at all Helper-aware renderers
        foreach ($container->findTaggedServiceIds($this->helperTagName) as $serviceId => $tags) {
            $container->findDefinition($serviceId)->setPublic(true);

            foreach (array_filter($tags) as $attributes) {
                $this->validateTag($serviceId, $attributes);
                $this->registerHelper(
                    $attributes['identifier'],
                    sprintf('%s::%s', $serviceId, $attributes['method'])
                );
            }
        }
    }

    private function registerHelper(string $name, string $function): void
    {
        foreach ($this->rendererDefinitions as $rendererDefinition) {
            $rendererDefinition->addMethodCall('registerHelper', [$name, $function]);
        }
    }

    protected function fetchRendererDefinitions(ContainerBuilder $container): void
    {
        $this->rendererDefinitions = [];

        foreach (array_keys($container->findTaggedServiceIds($this->rendererTagName)) as $serviceId) {
            $rendererDefinition = $container->findDefinition($serviceId);
            $rendererClass = $rendererDefinition->getClass();

            if (null !== $rendererClass && in_array(HelperAwareInterface::class, class_implements($rendererClass))) {
                $this->rendererDefinitions[] = $rendererDefinition;
            }
        }
    }

    /**
     * @param string $serviceId
     * @param array<string, string> $tagAttributes
     */
    private function validateTag(string $serviceId, array $tagAttributes): void
    {
        if (!array_key_exists('identifier', $tagAttributes) || '' === (string)$tagAttributes['identifier']) {
            throw new \InvalidArgumentException(
                sprintf('Service tag "%s" requires an identifier attribute to be defined, missing in: %s', $this->helperTagName, $serviceId),
                1606236820
            );
        }
        if (!array_key_exists('method', $tagAttributes) || '' === (string)$tagAttributes['method']) {
            throw new \InvalidArgumentException(
                sprintf('Service tag "%s" requires an method attribute to be defined, missing in: %s', $this->helperTagName, $serviceId),
                1606245140
            );
        }
    }
}
