<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2021 Elias Häußler <e.haeussler@familie-redlich.de>
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

namespace Fr\Typo3Handlebars\DependencyInjection\Compatibility;

use Fr\Typo3Handlebars\Compatibility\View\HandlebarsViewResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * ExtbaseControllerCompatibilityLayer
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 */
final class ExtbaseControllerCompatibilityLayer implements CompatibilityLayerInterface
{
    public const TYPE = 'extbase_controller';

    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var Definition
     */
    private $viewResolverDefinition;

    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
        $this->viewResolverDefinition = $container->getDefinition(HandlebarsViewResolver::class);
    }

    public function provide(string $processorServiceId, array $configuration): bool
    {
        $this->validateConfiguration($configuration);

        $controller = $configuration['controller'];
        $controllerDefinition = $this->container->getDefinition($controller);
        $controllerClassName = $controllerDefinition->getClass();

        $actions = GeneralUtility::trimExplode(',', $configuration['actions'] ?? '_all', true);
        $actionMap = array_fill_keys($actions, new Reference($processorServiceId));

        // Initialize processor map
        try {
            $processorMap = $this->viewResolverDefinition->getArgument('$processorMap');
        } catch (\Exception $exception) {
            $processorMap = [];
        }

        // Merge processor maps
        $processorMap[$controllerClassName] = array_replace($processorMap[$controllerClassName] ?? [], $actionMap);

        // Apply processor map and register method call
        $this->viewResolverDefinition->setArgument('$processorMap', $processorMap);
        $controllerDefinition->removeMethodCall('injectViewResolver');
        $controllerDefinition->addMethodCall('injectViewResolver', [new Reference($this->viewResolverDefinition->getClass())]);

        return true;
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private function validateConfiguration(array $configuration): void
    {
        if (!isset($configuration['controller']) || '' === (string)$configuration['controller']) {
            throw new \InvalidArgumentException(
                sprintf('An extbase controller must be configured for the "%s" compatibility layer.', self::TYPE),
                1632814271
            );
        }
        if (!$this->container->hasDefinition($configuration['controller'])) {
            throw new \OutOfBoundsException(
                sprintf('Unable to find extbase controller "%s" in service container.', $configuration['controller']),
                1632814362
            );
        }
        if (null === $this->container->getDefinition($configuration['controller'])->getClass()) {
            throw new \InvalidArgumentException(
                sprintf('Unable to determine class name for extbase controller with service id "%s".', $configuration['controller']),
                1632814520
            );
        }
        if (!in_array(ActionController::class, class_parents($this->container->getDefinition($configuration['controller'])->getClass()))) {
            throw new \InvalidArgumentException(
                sprintf('Only extbase controllers extending from "%s" are supported, found in: %s', ActionController::class, $configuration['controller']),
                1632814592
            );
        }
        if (isset($configuration['actions']) && !is_string($configuration['actions']) && null !== $configuration['actions']) {
            throw new \InvalidArgumentException(
                sprintf('Actions for extbase controllers must be configured as comma-separated list, %s given.', gettype($configuration['actions'])),
                1632814413
            );
        }
    }
}
