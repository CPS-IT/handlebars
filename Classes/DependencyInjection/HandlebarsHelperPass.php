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

use Fr\Typo3Handlebars\Renderer\HandlebarsRenderer;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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
    private $tagName;

    /**
     * @var string
     */
    private $rendererServiceId;

    public function __construct(string $tagName, string $rendererId = HandlebarsRenderer::class)
    {
        $this->tagName = $tagName;
        $this->rendererServiceId = $rendererId;
    }

    public function process(ContainerBuilder $container): void
    {
        $handlebarsRendererDefinition = $container->findDefinition($this->rendererServiceId);

        foreach ($container->findTaggedServiceIds($this->tagName) as $serviceName => $tags) {
            $container->findDefinition($serviceName)->setPublic(true);

            foreach (array_filter($tags) as $attributes) {
                if (!array_key_exists('identifier', $attributes) || (string)$attributes['identifier'] === '') {
                    throw new \InvalidArgumentException(
                        'Service tag "' . $this->tagName . '" requires an identifier attribute to be defined, missing in: ' . $serviceName,
                        1606236820
                    );
                }
                if (!array_key_exists('method', $attributes) || (string)$attributes['method'] === '') {
                    throw new \InvalidArgumentException(
                        'Service tag "' . $this->tagName . '" requires an method attribute to be defined, missing in: ' . $serviceName,
                        1606245140
                    );
                }

                // Register Handlebars helpers globally
                $identifier = $attributes['identifier'];
                $method = $attributes['method'];
                $handlebarsRendererDefinition->addMethodCall('registerHelper', [
                    $identifier,
                    $serviceName . '::' . $method,
                ]);
            }
        }
    }
}
