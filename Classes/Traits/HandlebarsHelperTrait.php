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

use Fr\Typo3Handlebars\Exception\InvalidHelperException;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * HandlebarsHelperTrait
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
trait HandlebarsHelperTrait
{
    /**
     * @var array<string, callable>
     */
    protected $helpers = [];

    /**
     * @param mixed $function
     */
    public function registerHelper(string $name, $function): void
    {
        try {
            $this->helpers[$name] = $this->resolveHelperFunction($function);
        } catch (InvalidHelperException | \ReflectionException $exception) {
            if ($this->logger instanceof LoggerInterface) {
                $this->logger->critical(
                    'Error while registering Handlebars helper "' . $name . '".',
                    ['name' => $name, 'function' => $function, 'exception' => $exception]
                );
            }
        }
    }

    /**
     * @return array<string, callable>
     */
    public function getHelpers(): array
    {
        return $this->helpers;
    }

    /**
     * @param mixed $function
     * @throws InvalidHelperException
     * @throws \ReflectionException
     */
    protected function resolveHelperFunction($function): callable
    {
        // Try to resolve the Helper function in this order:
        //
        // 1. callable
        // 2. invokable class
        // ├─ a. as string (class-name)
        // └─ b. as object
        // 3. class method
        // ├─ a. as string => class-name::method-name
        // ├─ b. as array => [class-name, method-name]
        // └─ c. as initialized array => [object, method-name]

        $className = null;
        $methodName = null;

        if (\is_string($function) && !str_contains($function, '::')) {
            // 1. callable
            if (\is_callable($function)) {
                return $function;
            }

            // 2a. invokable class as string
            if (class_exists($function) && \is_callable($callable = GeneralUtility::makeInstance($function))) {
                return $callable;
            }
        }

        // 2b. invokable class as object
        if (\is_object($function) && \is_callable($function)) {
            return $function;
        }

        // 3a. class method as string
        if (\is_string($function) && str_contains($function, '::')) {
            [$className, $methodName] = explode('::', $function, 2);
        }

        // 3b. class method as array
        // 3c. class method as initialized array
        if (\is_array($function) && 2 === \count($function)) {
            [$className, $methodName] = $function;
        }

        // Early return if either class name or method name cannot be resolved
        if (null === $className || null === $methodName) {
            throw InvalidHelperException::forUnsupportedType($function);
        }

        // Early return if method is not public
        $reflectionClass = new \ReflectionClass($className);
        $reflectionMethod = $reflectionClass->getMethod($methodName);
        if (!$reflectionMethod->isPublic()) {
            throw InvalidHelperException::forFunction($className . '::' . $methodName);
        }

        // Check if method can be called statically
        $callable = [$className, $methodName];
        if ($reflectionMethod->isStatic() && \is_callable($callable)) {
            return $callable;
        }

        // Instantiate class if not done yet
        /** @var class-string $className */
        if (\is_string($className)) {
            $className = GeneralUtility::makeInstance($className);
        }

        $callable = [$className, $methodName];
        if (\is_callable($callable)) {
            return $callable;
        }

        throw InvalidHelperException::forInvalidCallable($callable);
    }

    /**
     * @param mixed $helperFunction
     * @codeCoverageIgnore
     * @deprecated use resolveHelperFunction() instead and check for thrown exceptions
     */
    protected function isValidHelper($helperFunction): bool
    {
        trigger_error(
            sprintf(
                'The method "%s" is deprecated and will be removed with 0.9.0. ' .
                'Use "%s::resolveHelperFunction()" instead and check for thrown exceptions.',
                __METHOD__,
                __TRAIT__
            ),
            E_USER_DEPRECATED
        );

        try {
            return (bool)$this->resolveHelperFunction($helperFunction);
        } catch (InvalidHelperException | \ReflectionException $e) {
            return false;
        }
    }
}
