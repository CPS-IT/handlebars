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

use Fr\Typo3Handlebars\Attribute\AsHelper;
use Fr\Typo3Handlebars\DependencyInjection\Extension\HandlebarsExtension;
use Fr\Typo3Handlebars\Renderer\Helper\Helper;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $container): void {
    $container->registerExtension(new HandlebarsExtension());
    $container->addCompilerPass(new DataProcessorPass('handlebars.processor', 'handlebars.compatibility_layer'));
    $container->addCompilerPass(new HandlebarsHelperPass(AsHelper::TAG_NAME));
    $container->addCompilerPass(new FeatureRegistrationPass(), priority: 30);

    $container->registerAttributeForAutoconfiguration(
        AsHelper::class,
        static function (ChildDefinition $definition, AsHelper $attribute, \Reflector $reflector): void {
            $definition->addTag(
                AsHelper::TAG_NAME,
                [
                    'identifier' => $attribute->identifier,
                    'method' => $attribute->method ?? (
                        $reflector instanceof \ReflectionMethod
                        ? $reflector->getName()
                        : (
                            $reflector instanceof \ReflectionClass && $reflector->implementsInterface(Helper::class)
                            ? 'render'
                            : '__invoke'
                        )
                    ),
                ],
            );
        },
    );
};
