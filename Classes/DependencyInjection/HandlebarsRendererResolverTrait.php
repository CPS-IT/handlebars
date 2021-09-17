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

namespace Fr\Typo3Handlebars\DependencyInjection;

use Fr\Typo3Handlebars\Renderer\HelperAwareInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * HandlebarsRendererResolverTrait
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 */
trait HandlebarsRendererResolverTrait
{
    /**
     * @param ServiceLocator $rendererLocator
     * @param string $groupBy
     * @return array[]
     */
    private function getHelpersOfRegisteredRenderers(ServiceLocator $rendererLocator, string $groupBy = 'helper'): array
    {
        $helpers = [];

        foreach ($rendererLocator->getProvidedServices() as $serviceId => $rendererClassName) {
            if (!in_array(HelperAwareInterface::class, class_implements($rendererClassName))) {
                continue;
            }

            $renderer = $rendererLocator->get($serviceId);
            foreach ($renderer->getHelpers() as $name => $function) {
                $helperDefinition = [
                    'renderer' => $rendererClassName,
                    'callable' => $function,
                ];

                if ('renderer' === $groupBy) {
                    $helpers[$serviceId][$name] = $helperDefinition;
                } else {
                    $helpers[$name][$serviceId] = $helperDefinition;
                }
            }
        }

        return $helpers;
    }
}
