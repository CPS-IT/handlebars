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

use Fr\Typo3Handlebars\Configuration;
use Fr\Typo3Handlebars\Renderer;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Core;

/**
 * FeatureRegistrationPass
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 */
final class FeatureRegistrationPass implements DependencyInjection\Compiler\CompilerPassInterface
{
    private DependencyInjection\ContainerBuilder $container;
    private Core\Configuration\ExtensionConfiguration $extensionConfiguration;

    public function process(DependencyInjection\ContainerBuilder $container): void
    {
        $this->container = $container;
        $this->extensionConfiguration = $this->container->get(Core\Configuration\ExtensionConfiguration::class);

        if ($this->isFeatureEnabled('blockHelper')) {
            $this->activateHelper('block', Renderer\Helper\BlockHelper::class);
        }
        if ($this->isFeatureEnabled('contentHelper')) {
            $this->activateHelper('content', Renderer\Helper\ContentHelper::class);
        }
        if ($this->isFeatureEnabled('extendHelper')) {
            $this->activateHelper('extend', Renderer\Helper\ExtendHelper::class);
        }
        if ($this->isFeatureEnabled('renderHelper')) {
            $this->activateHelper('render', Renderer\Helper\RenderHelper::class);
        }
        if ($this->isFeatureEnabled('flatTemplateResolver')) {
            $this->activateFlatTemplateResolver();
        }
    }

    /**
     * @param class-string<Renderer\Helper\Helper> $className
     */
    private function activateHelper(string $name, string $className): void
    {
        $definition = $this->container->getDefinition($className);
        $definition->addTag('handlebars.helper', [
            'identifier' => $name,
            'method' => 'render',
        ]);
    }

    private function activateFlatTemplateResolver(): void
    {
        $this->container->getDefinition('handlebars.template_resolver')->setClass(Renderer\Template\FlatTemplateResolver::class);
    }

    private function isFeatureEnabled(string $featureName): bool
    {
        $configurationPath = sprintf('features/%s/enable', $featureName);

        // Avoid calls to PackageManager during testing
        if (Core\Core\Environment::getContext()->isTesting()) {
            return true;
        }

        try {
            return (bool)$this->extensionConfiguration->get(Configuration\Extension::KEY, $configurationPath);
        } catch (Core\Exception) {
            return false;
        }
    }
}
