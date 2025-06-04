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

use Fr\Typo3Handlebars\DataProcessing\DataProcessor;
use Fr\Typo3Handlebars\DependencyInjection\Compatibility\ProcessorCompatibility;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * DataProcessorPass
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 * @codeCoverageIgnore
 */
final readonly class DataProcessorPass implements CompilerPassInterface
{
    public function __construct(
        private string $processorTagName,
        private string $compatibilityTagName,
    ) {}

    public function process(ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(DataProcessor::class)->addTag($this->processorTagName);

        foreach ($container->findTaggedServiceIds($this->processorTagName) as $id => $tags) {
            $service = $container->findDefinition($id);
            $service->setPublic(true);

            // Autowire related presenters and providers
            $processingBridge = new ProcessingBridge($id, $service);
            if (!$processingBridge->hasMethodCall('setPresenter')) {
                $presenterService = $processingBridge->getPresenter();
                $service->addMethodCall('setPresenter', [$presenterService]);
            }
            if (!$processingBridge->hasMethodCall('setProvider')) {
                $providerService = $processingBridge->getProvider();
                $service->addMethodCall('setProvider', [$providerService]);
            }

            // Build compatibility layers
            $compatibilityTags = $container->getDefinition($id)->getTag($this->compatibilityTagName);
            foreach (array_filter($compatibilityTags) as $attributes) {
                $processorCompatibility = new ProcessorCompatibility($id, $attributes, $container);
                $processorCompatibility->provideCompatibility();
            }
        }
    }
}
