<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2024 Elias Häußler <e.haeussler@familie-redlich.de>
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
     * @param class-string<Renderer\Helper\HelperInterface> $className
     */
    private function activateHelper(string $name, string $className, string $methodName = 'evaluate'): void
    {
        $definition = $this->container->getDefinition($className);
        $definition->addTag('handlebars.helper', [
            'identifier' => $name,
            'method' => $methodName,
        ]);
    }

    private function activateFlatTemplateResolver(): void
    {
        $this->container->getDefinition('handlebars.template_resolver')->setClass(Renderer\Template\FlatTemplateResolver::class);
        $this->container->getDefinition('handlebars.partial_resolver')->setClass(Renderer\Template\FlatTemplateResolver::class);
    }

    private function isFeatureEnabled(string $featureName): bool
    {
        $configurationPath = sprintf('features/%s/enable', $featureName);

        try {
            return (bool)$this->extensionConfiguration->get(Configuration\Extension::KEY, $configurationPath);
        } catch (Core\Exception) {
            return false;
        }
    }
}