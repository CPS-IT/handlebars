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

namespace CPSIT\Typo3Handlebars\DependencyInjection;

use CPSIT\Typo3Handlebars\Attribute;
use CPSIT\Typo3Handlebars\Extbase\View\ExtbaseHandlebarsViewFactory;
use CPSIT\Typo3Handlebars\Renderer;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Extbase;

return static function (
    DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator,
    DependencyInjection\ContainerBuilder $container,
): void {
    $container->registerExtension(new Extension\HandlebarsExtension());
    $container->addCompilerPass(new HandlebarsHelperPass());

    $container->registerForAutoconfiguration(Extbase\Mvc\Controller\ActionController::class)
        ->addMethodCall(
            'injectViewFactory',
            [new DependencyInjection\Reference(ExtbaseHandlebarsViewFactory::class)],
        )
    ;

    $container->registerAttributeForAutoconfiguration(
        Attribute\AsHelper::class,
        static function (DependencyInjection\ChildDefinition $definition, Attribute\AsHelper $attribute, \Reflector $reflector): void {
            if ($attribute->method !== null) {
                $method = $attribute->method;
            } elseif ($reflector instanceof \ReflectionMethod) {
                $method = $reflector->getName();
            } elseif ($reflector instanceof \ReflectionClass && $reflector->implementsInterface(Renderer\Helper\Helper::class)) {
                $method = 'render';
            } else {
                $method = '__invoke';
            }

            $definition->addTag(
                HandlebarsHelperPass::TAG_NAME,
                [
                    'identifier' => $attribute->identifier,
                    'method' => $method,
                ],
            );
        },
    );
};
