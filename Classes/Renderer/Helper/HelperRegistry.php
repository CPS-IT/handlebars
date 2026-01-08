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

namespace CPSIT\Typo3Handlebars\Renderer\Helper;

use CPSIT\Typo3Handlebars\Exception;
use CPSIT\Typo3Handlebars\Renderer;
use DevTheorem\Handlebars;
use Psr\Log;
use TYPO3\CMS\Core;

/**
 * HelperRegistry
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class HelperRegistry implements Core\SingletonInterface
{
    /**
     * @var array<string, \Closure(mixed..., Handlebars\HelperOptions): mixed>
     */
    private array $helpers = [];

    public function __construct(
        private readonly Log\LoggerInterface $logger,
    ) {}

    /**
     * @param array{object, string}|array{class-string, string}|callable|string|Helper $function
     */
    public function add(string $name, array|callable|string|Helper $function): void
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
     * @return \Closure(mixed..., Handlebars\HelperOptions): mixed
     * @throws Exception\HelperIsNotRegistered
     */
    public function get(string $name): \Closure
    {
        if (!isset($this->helpers[$name])) {
            throw new Exception\HelperIsNotRegistered($name);
        }

        return $this->helpers[$name];
    }

    /**
     * @return array<string, \Closure(mixed..., Handlebars\HelperOptions): mixed>
     */
    public function getAll(): array
    {
        return $this->helpers;
    }

    public function has(string $name): bool
    {
        return isset($this->helpers[$name]);
    }

    /**
     * @param array{object, string}|array{class-string, string}|callable|string|Helper $function
     * @throws Exception\InvalidHelperException
     * @throws \ReflectionException
     */
    private function resolveHelperFunction(array|callable|string|Helper $function): callable
    {
        // Try to resolve the Helper function in this order:
        //
        // 1. callable
        // ├─ a. as string
        // └─ b. as closure or first class callable syntax
        // 2. invokable class
        // └─ a. as string (class-name)
        // └─ b. as object
        // 3. class implementing Helper interface
        // ├─ a. as string (class-name)
        // └─ b. as object
        // 4. class method
        // ├─ a. as string => class-name::method-name
        // ├─ b. as array => [class-name, method-name]
        // └─ c. as initialized array => [object, method-name]

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
            if (class_exists($function) && \is_a($function, Helper::class, true)) {
                return Core\Utility\GeneralUtility::makeInstance($function)->render(...);
            }
        }

        if ($function instanceof \Closure) {
            // 1b. callable as closure or first class callable syntax
            return $function;
        }

        if (is_callable($function) && is_object($function)) {
            // 2b. invokable class as object
            return $function;
        }

        // 3b. class implementing Helper interface as object
        if ($function instanceof Helper) {
            return $function->render(...);
        }

        $className = null;
        $methodName = null;

        // 4a. class method as string
        /* @phpstan-ignore booleanAnd.rightAlwaysFalse */
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
            throw Exception\InvalidHelperException::forFunction($reflectionClass->name . '::' . $methodName);
        }

        // Instantiate class if not done yet
        if (\is_string($className) && !$reflectionMethod->isStatic()) {
            /** @var class-string $className */
            $helperClass = Core\Utility\GeneralUtility::makeInstance($className);
        } else {
            $helperClass = $className;
        }

        $callable = [$helperClass, $methodName];

        if (!\is_callable($callable)) {
            throw Exception\InvalidHelperException::forInvalidCallable($callable);
        }

        if ($reflectionMethod->isStatic()) {
            return $helperClass::$methodName(...);
        }

        return $helperClass->$methodName(...);
    }

    /**
     * @return \Closure(mixed..., Handlebars\HelperOptions): mixed
     */
    private function decorateHelperFunction(callable $function): \Closure
    {
        return static function () use ($function) {
            $arguments = \func_get_args();
            /** @var Handlebars\HelperOptions $options */
            $options = \array_pop($arguments);
            $renderingContext = $options->data['renderingContext'] ?? null;
            $parameters = self::mapFunctionParameters($function, $options, $renderingContext, $arguments);

            return $function(...$parameters);
        };
    }

    /**
     * @param list<mixed> $arguments
     * @return list<mixed>
     * @throws Exception\InvalidHelperException
     */
    private static function mapFunctionParameters(
        callable $function,
        Handlebars\HelperOptions $options,
        mixed $renderingContext,
        array $arguments,
    ): array {
        // Fall back to HelperOptions + runtime arguments if callable cannot be reflected
        if (!is_string($function) && !($function instanceof \Closure)) {
            return [$options, ...$arguments];
        }

        $reflectionFunction = new \ReflectionFunction($function);
        $parameters = [];
        $parameterMap = [
            Handlebars\HelperOptions::class => $options,
            Renderer\RenderingContext::class => $renderingContext,
        ];

        foreach ($reflectionFunction->getParameters() as $parameter) {
            $type = $parameter->getType();

            // Exit loop if we reached runtime arguments (those may be declared without a named type)
            if (!($type instanceof \ReflectionNamedType)) {
                break;
            }

            // Exit loop if we reached runtime arguments
            if (!\array_key_exists($type->getName(), $parameterMap)) {
                break;
            }

            $resolvedParameter = $parameterMap[$type->getName()];

            if ($resolvedParameter === null) {
                if ($type->allowsNull()) {
                    $parameters[] = null;
                    continue;
                }

                // Fail if a non-nullable parameter would receive null
                throw Exception\InvalidHelperException::forUnresolvableParameter($function, $parameter->getName());
            }

            // Fail if a parameter would receive a wrong type
            if (!\is_a($resolvedParameter, $type->getName(), true)) {
                throw Exception\InvalidHelperException::forUnresolvableParameter($function, $parameter->getName());
            }

            $parameters[] = $resolvedParameter;
        }

        return [...$parameters, ...$arguments];
    }
}
