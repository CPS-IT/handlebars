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
