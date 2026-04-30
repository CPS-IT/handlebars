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
use CPSIT\Typo3Handlebars\Renderer;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Frontend;

return static function (
    DependencyInjection\ContainerBuilder $container,
    DependencyInjection\Loader\Configurator\ContainerConfigurator $configurator,
): void {
    $container->registerExtension(new Extension\HandlebarsExtension());
    $container->addCompilerPass(new HandlebarsHelperPass());
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

    // Make sure various services receive an instance of HandlebarsViewFactory, because they *may*
    // render Handelbars templates and can easily fall back to Fluid views using the configured
    // delegate to FluidViewAdapter. All other services must still rely on FluidViewAdapter,
    // because we cannot safely delegate to FluidViewAdapter in our HandlebarsViewFactory due to
    // missing knowledge of the exact context (the HandlebarsViewFactory itself cannot decide
    // whether a Handlebars template actually exists, so the fallback to the FluidViewFactory may
    // not be triggered in this case, which will fail hard on various services that rely on
    // FluidViewAdapter being returned by the ViewFactoryInterface implementation).
    $viewFactoryPass = new CompilerPass\HandlebarsViewFactoryPass();
    $viewFactoryPass->addMethodCall(Extbase\Mvc\Controller\ActionController::class, 'injectViewFactory');
    $viewFactoryPass->addProperty(Frontend\ContentObject\PageViewContentObject::class);

    $container->addCompilerPass($viewFactoryPass, DependencyInjection\Compiler\PassConfig::TYPE_BEFORE_OPTIMIZATION, -100);
};
