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
use TYPO3\CMS\Fluid;
use TYPO3\CMS\Frontend;
use TYPO3\CMS\Seo;

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

    // Make sure various services always receive an instance of FluidViewFactory, because they fail
    // hard if any other view than FluidViewAdapter is resolved, which cannot be assured when using
    // our custom HandlebarsViewFactory, as we don't know the exact context.
    $services = $configurator->services();
    $fluidViewFactoryReference = new DependencyInjection\Reference(Fluid\View\FluidViewFactory::class);
    $servicesRequestingFluidViewFactory = [
        Frontend\ContentObject\FluidTemplateContentObject::class => '$viewFactory',
        Seo\XmlSitemap\XmlSitemapRenderer::class => '$viewFactory',
    ];

    foreach ($servicesRequestingFluidViewFactory as $className => $argumentName) {
        if (class_exists($className)) {
            $services->get($className)->arg($argumentName, $fluidViewFactoryReference);
        }
    }
};
