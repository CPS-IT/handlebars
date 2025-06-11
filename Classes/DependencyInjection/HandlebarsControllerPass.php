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

use Fr\Typo3Handlebars\Extbase;
use Symfony\Component\DependencyInjection;

/**
 * HandlebarsControllerPass
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 */
final readonly class HandlebarsControllerPass implements DependencyInjection\Compiler\CompilerPassInterface
{
    public const TAG_NAME = 'handlebars.extbase_controller';

    public function process(DependencyInjection\ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(Extbase\View\ExtbaseHandlebarsViewFactory::class)) {
            return;
        }

        $viewFactory = new DependencyInjection\Reference(Extbase\View\ExtbaseHandlebarsViewFactory::class);

        foreach ($container->findTaggedServiceIds(self::TAG_NAME) as $id => $tags) {
            $service = $container->findDefinition($id);
            $service->addMethodCall('injectViewFactory', [$viewFactory]);
        }
    }
}
