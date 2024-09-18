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
use Fr\Typo3Handlebars\DataProcessing\DataProcessorInterface;
use Fr\Typo3Handlebars\Exception\InvalidClassException;
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
        $this->validateService(HandlebarsViewResolver::class);
    }

    public function provide(string $processorServiceId, array $configuration): bool
    {
        $this->validateConfiguration($configuration);

        $controller = $configuration['controller'];
        $controllerDefinition = $this->container->getDefinition($controller);
        /** @var class-string $controllerClassName */
        $controllerClassName = $controllerDefinition->getClass();

        // Validate controller class name
        $this->validateService($controller);

        $actions = GeneralUtility::trimExplode(',', $configuration['actions'] ?? '_all', true);
        $actionMap = array_fill_keys($actions, new Reference($processorServiceId));

        // Merge and apply processor map
        $processorMap = $this->buildProcessorMap($controllerClassName, $actionMap);
        $this->viewResolverDefinition->removeMethodCall('setProcessorMap');
        $this->viewResolverDefinition->addMethodCall('setProcessorMap', [$processorMap]);
        /** @var class-string $viewResolverClassName */
        $viewResolverClassName = $this->viewResolverDefinition->getClass();

        // Apply processor map and register method call
        $controllerDefinition->removeMethodCall('injectViewResolver');
        $controllerDefinition->addMethodCall('injectViewResolver', [new Reference($viewResolverClassName)]);

        return true;
    }

    /**
     * @param class-string $controllerClassName
     * @param array<string, Reference> $actionMap
     * @return array<string, array<string, DataProcessorInterface>>
     */
    private function buildProcessorMap(string $controllerClassName, array $actionMap): array
    {
        $processorMap = [];

        foreach ($this->viewResolverDefinition->getMethodCalls() as $call) {
            if ($call[0] === 'setProcessorMap') {
                $processorMap = $call[1][0];
            }
        }

        $processorMap[$controllerClassName] = array_replace($processorMap[$controllerClassName] ?? [], $actionMap);

        return $processorMap;
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private function validateConfiguration(array $configuration): void
    {
        if (!isset($configuration['controller']) || (string)$configuration['controller'] === '') {
            throw new \InvalidArgumentException(
                \sprintf('An extbase controller must be configured for the "%s" compatibility layer.', self::TYPE),
                1632814271
            );
        }
        if (!$this->container->hasDefinition($configuration['controller'])) {
            throw new \OutOfBoundsException(
                \sprintf('Unable to find extbase controller "%s" in service container.', $configuration['controller']),
                1632814362
            );
        }
        if ($this->container->getDefinition($configuration['controller'])->getClass() === null) {
            throw new \InvalidArgumentException(
                \sprintf('Unable to determine class name for extbase controller with service id "%s".', $configuration['controller']),
                1632814520
            );
        }
        if (!\in_array(ActionController::class, class_parents($this->container->getDefinition($configuration['controller'])->getClass()) ?: [])) {
            throw new \InvalidArgumentException(
                \sprintf('Only extbase controllers extending from "%s" are supported, found in: %s', ActionController::class, $configuration['controller']),
                1632814592
            );
        }
        if (isset($configuration['actions']) && !\is_string($configuration['actions']) && $configuration['actions'] !== null) {
            throw new \InvalidArgumentException(
                \sprintf('Actions for extbase controllers must be configured as comma-separated list, %s given.', \gettype($configuration['actions'])),
                1632814413
            );
        }
    }

    private function validateService(string $serviceId): void
    {
        $definition = $this->container->findDefinition($serviceId);
        /** @var class-string|null $className */
        $className = $definition->getClass();

        if ($className === null) {
            throw InvalidClassException::forService($serviceId);
        }
        if (!class_exists($className)) {
            throw InvalidClassException::create($className);
        }
    }
}
