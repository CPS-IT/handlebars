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

use CPSIT\Typo3Handlebars\Renderer;
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
    public const TAG_NAME = 'handlebars.helper';

    public function process(DependencyInjection\ContainerBuilder $container): void
    {
        $registryDefinition = $container->getDefinition(Renderer\Helper\HelperRegistry::class);

        // Register tagged Handlebars helper at all Helper-aware renderers
        foreach ($container->findTaggedServiceIds(self::TAG_NAME) as $serviceId => $tags) {
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
        if (!array_key_exists('identifier', $tagAttributes) || (string)$tagAttributes['identifier'] === '') {
            throw new \InvalidArgumentException(
                sprintf('Service tag "%s" requires an identifier attribute to be defined, missing in: %s', self::TAG_NAME, $serviceId),
                1606236820,
            );
        }
        if (!array_key_exists('method', $tagAttributes) || (string)$tagAttributes['method'] === '') {
            throw new \InvalidArgumentException(
                sprintf('Service tag "%s" requires an method attribute to be defined, missing in: %s', self::TAG_NAME, $serviceId),
                1606245140,
            );
        }
    }
}
