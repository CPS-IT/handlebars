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

namespace CPSIT\Typo3Handlebars\DependencyInjection\CompilerPass;

use CPSIT\Typo3Handlebars\View;
use Symfony\Component\DependencyInjection;

/**
 * HandlebarsViewFactoryPass
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 *
 * @phpstan-type TargetService array{calls?: array<string, string>, properties?: list<string>}
 */
final class HandlebarsViewFactoryPass extends DependencyInjection\Compiler\AbstractRecursivePass
{
    private ?DependencyInjection\Reference $viewFactoryReference = null;

    /**
     * @var array<string, TargetService>
     */
    private array $targetServices = [];

    public function __construct(
        private readonly string $viewFactoryServiceId = View\HandlebarsViewFactory::class,
    ) {}

    public function process(DependencyInjection\ContainerBuilder $container): void
    {
        if (!$container->has($this->viewFactoryServiceId)) {
            return;
        }

        $this->viewFactoryReference = new DependencyInjection\Reference($this->viewFactoryServiceId);

        try {
            parent::process($container);
        } finally {
            $this->viewFactoryReference = null;
        }
    }

    protected function processValue(mixed $value, bool $isRoot = false): mixed
    {
        $value = parent::processValue($value, $isRoot);

        if (!($value instanceof DependencyInjection\Definition)
            || $this->currentId === null
            || $this->viewFactoryReference === null
            || !$value->isAutowired()
            || $value->isAbstract()
            || $value->getClass() === null
        ) {
            return $value;
        }

        $configuration = $this->targetServices[$this->currentId] ?? $this->resolveBaseConfiguration($value->getClass());

        if ($configuration === null) {
            return $value;
        }

        foreach ($configuration['calls'] ?? [] as $methodName => $argumentName) {
            $value->removeMethodCall($methodName);
            $value->addMethodCall($methodName, [$argumentName => $this->viewFactoryReference]);
        }

        foreach ($configuration['properties'] ?? [] as $propertyName) {
            $value->setArgument($propertyName, $this->viewFactoryReference);
        }

        return $value;
    }

    /**
     * @return TargetService|null
     */
    private function resolveBaseConfiguration(string $className): ?array
    {
        $reflection = $this->container?->getReflectionClass($className, false);

        if ($reflection === null) {
            return null;
        }

        $current = $reflection;

        do {
            if (array_key_exists($current->getName(), $this->targetServices)) {
                return $this->targetServices[$current->getName()];
            }

            $current = $current->getParentClass();
        } while ($current !== false);

        return null;
    }

    public function addMethodCall(string $serviceId, string $methodName, string $argumentName = '$viewFactory'): self
    {
        $this->targetServices[$serviceId] ??= [];
        $this->targetServices[$serviceId]['calls'] ??= [];
        $this->targetServices[$serviceId]['calls'][$methodName] = $argumentName;

        return $this;
    }

    public function addProperty(string $serviceId, string $propertyName = '$viewFactory'): self
    {
        $this->targetServices[$serviceId] ??= [];
        $this->targetServices[$serviceId]['properties'] ??= [];
        $this->targetServices[$serviceId]['properties'][] = $propertyName;

        return $this;
    }
}
