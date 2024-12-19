<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2020 Elias Häußler <e.haeussler@familie-redlich.de>
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

namespace Fr\Typo3Handlebars\Traits;

use Fr\Typo3Handlebars\Exception;
use Fr\Typo3Handlebars\Renderer;
use TYPO3\CMS\Core;

/**
 * HandlebarsHelperTrait
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
trait HandlebarsHelperTrait
{
    /**
     * @var array<string, callable(Renderer\Helper\Context\HelperContext): mixed>
     */
    protected array $helpers = [];

    public function registerHelper(string $name, mixed $function): void
    {
        try {
            $this->helpers[$name] = $this->decorateHelperFunction(
                $this->resolveHelperFunction($function),
            );
        } catch (Exception\InvalidHelperException | \ReflectionException $exception) {
            $this->logger->critical(
                'Error while registering Handlebars helper "' . $name . '".',
                [
                    'name' => $name,
                    'function' => $function,
                    'exception' => $exception,
                ],
            );
        }
    }

    /**
     * @return array<string, callable(Renderer\Helper\Context\HelperContext): mixed>
     */
    public function getHelpers(): array
    {
        return $this->helpers;
    }

    /**
     * @throws Exception\InvalidHelperException
     * @throws \ReflectionException
     */
    protected function resolveHelperFunction(mixed $function): callable
    {
        // Try to resolve the Helper function in this order:
        //
        // 1. callable
        // ├─ a. as string
        // └─ b. as closure or first class callable syntax
        // 2. invokable class
        // ├─ a. as string (class-name)
        // └─ b. as object
        // 3. class implementing Helper interface
        // ├─ a. as string (class-name)
        // └─ b. as object
        // 4. class method
        // ├─ a. as string => class-name::method-name
        // ├─ b. as array => [class-name, method-name]
        // └─ c. as initialized array => [object, method-name]

        $className = null;
        $methodName = null;

        if (\is_string($function) && !str_contains($function, '::')) {
            // 1a. callable as string
            if (\is_callable($function)) {
                return $function;
            }

            // 2a. invokable class as string
            if (class_exists($function) && \is_callable($callable = Core\Utility\GeneralUtility::makeInstance($function))) {
                return $callable;
            }

            // 3a. class implementing Helper interface as string
            if (class_exists($function) && \is_a($function, Renderer\Helper\HelperInterface::class, true)) {
                return Core\Utility\GeneralUtility::makeInstance($function)->render(...);
            }
        }

        if (\is_callable($function)) {
            // 1b. callable as closure or first class callable syntax
            return $function;
        }

        if (\is_object($function)) {
            // 2b. invokable class as object
            if (\is_callable($function)) {
                return $function;
            }

            // 3b. class implementing Helper interface as object
            if ($function instanceof Renderer\Helper\HelperInterface) {
                return $function->render(...);
            }
        }

        // 4a. class method as string
        if (\is_string($function) && str_contains($function, '::')) {
            [$className, $methodName] = explode('::', $function, 2);
        }

        // 4b. class method as array
        // 4c. class method as initialized array
        if (\is_array($function) && \count($function) === 2) {
            [$className, $methodName] = $function;
        }

        // Early return if either class name or method name cannot be resolved
        if ($className === null || $methodName === null) {
            throw Exception\InvalidHelperException::forUnsupportedType($function);
        }

        // Early return if method is not public
        $reflectionClass = new \ReflectionClass($className);
        $reflectionMethod = $reflectionClass->getMethod($methodName);
        if (!$reflectionMethod->isPublic()) {
            throw Exception\InvalidHelperException::forFunction($className . '::' . $methodName);
        }

        // Check if method can be called statically
        $callable = [$className, $methodName];
        if ($reflectionMethod->isStatic() && \is_callable($callable)) {
            return $callable;
        }

        // Instantiate class if not done yet
        /** @var class-string $className */
        if (\is_string($className)) {
            $className = Core\Utility\GeneralUtility::makeInstance($className);
        }

        $callable = [$className, $methodName];
        if (\is_callable($callable)) {
            return $callable;
        }

        throw Exception\InvalidHelperException::forInvalidCallable($callable);
    }

    /**
     * @return callable(\Fr\Typo3Handlebars\Renderer\Helper\Context\HelperContext): mixed
     */
    protected function decorateHelperFunction(callable $function): callable
    {
        return static function () use ($function) {
            $arguments = \func_get_args();
            $context = Renderer\Helper\Context\HelperContext::fromRuntimeCall($arguments);

            return $function($context);
        };
    }

    /**
     * @codeCoverageIgnore
     * @deprecated use resolveHelperFunction() instead and check for thrown exceptions
     */
    protected function isValidHelper(mixed $helperFunction): bool
    {
        trigger_error(
            \sprintf(
                'The method "%s" is deprecated and will be removed with 0.9.0. ' .
                'Use "%s::resolveHelperFunction()" instead and check for thrown exceptions.',
                __METHOD__,
                __TRAIT__,
            ),
            E_USER_DEPRECATED,
        );

        try {
            return (bool)$this->resolveHelperFunction($helperFunction);
        } catch (Exception\InvalidHelperException | \ReflectionException) {
            return false;
        }
    }
}
