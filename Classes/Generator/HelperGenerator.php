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

namespace Fr\Typo3Handlebars\Generator;

use Fr\Typo3Handlebars\DependencyInjection\HandlebarsRendererResolverTrait;
use Fr\Typo3Handlebars\Exception\ConstraintViolationException;
use Fr\Typo3Handlebars\Generator\Definition\HelperClassDefinition;
use Fr\Typo3Handlebars\Generator\Resolver\ClassResolver;
use Fr\Typo3Handlebars\Generator\Result\GeneratedFile;
use Fr\Typo3Handlebars\Generator\Result\GeneratorResult;
use Fr\Typo3Handlebars\Generator\Writer\PhpWriter;
use Fr\Typo3Handlebars\Generator\Writer\YamlWriter;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * HelperGenerator
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 */
class HelperGenerator implements GeneratorInterface
{
    use FileGenerationTrait;
    use HandlebarsRendererResolverTrait;

    /**
     * @var YamlWriter
     */
    protected $yamlWriter;

    /**
     * @var string[]
     */
    protected $registeredHelpers;

    public function __construct(
        PhpWriter $phpWriter,
        YamlWriter $yamlWriter,
        ClassResolver $classResolver,
        ServiceLocator $rendererLocator
    ) {
        $this->phpWriter = $phpWriter;
        $this->yamlWriter = $yamlWriter;
        $this->classResolver = $classResolver;
        $this->registeredHelpers = array_keys($this->getHelpersOfRegisteredRenderers($rendererLocator));
    }

    /**
     * @param array<string, mixed> $options
     * @throws ConstraintViolationException
     */
    public function generate(string $name, array $options, bool $overwriteExistingFiles = false): GeneratorResult
    {
        if (\in_array($name, $this->registeredHelpers, true)) {
            throw ConstraintViolationException::createForUniqueHelper($name);
        }

        ['extensionKey' => $extensionKey, 'className' => $className, 'methodName' => $methodName] = $options;

        if (empty($extensionKey) || !ExtensionManagementUtility::isLoaded($extensionKey)) {
            throw ConstraintViolationException::createForUnsupportedExtension($extensionKey);
        }

        // Resolve class parts
        $classParts = $this->resolveClassParts($extensionKey, $name);
        if (null !== $className) {
            $classParts['className'] = $this->classResolver->sanitizeNamespacePart($className);
        }

        // Build helper class definition
        $classDefinitionBuilder = new HelperClassDefinition();
        $classDefinition = $classDefinitionBuilder->build($name, ['methodName' => $methodName]);

        // Generate helper class
        [$className, $classFilename, $templateResult, $previousContent] = $this->generateClass(
            $extensionKey,
            $classParts,
            $classDefinition,
            $overwriteExistingFiles
        );
        $classGenerated = true === $templateResult;

        $generatedClass = new GeneratedFile($classFilename, 'helperClass', ['className' => $className]);
        $generatedClass->setGenerated($classGenerated);
        if (\is_string($templateResult)) {
            $generatedClass->setContent($templateResult);
        }
        if (null !== $previousContent) {
            $generatedClass->setPreviousContent($previousContent);
        }

        // Generate Services.yaml
        try {
            [$servicesYamlFile, $servicesYamlResult] = $this->writeToServicesYaml(
                $extensionKey,
                $name,
                $className,
                $methodName,
                $overwriteExistingFiles
            );

            $servicesYamlGenerated = true === $servicesYamlResult;
        } catch (\Exception $exception) {
            // Remove generated class if Services.yaml could not be written
            if ($classGenerated) {
                $this->restoreGeneratedFile($generatedClass);
            }

            /** @noinspection PhpUnhandledExceptionInspection */
            throw $exception;
        }

        // Remove generated class if Services.yaml could not be written
        if (false === $servicesYamlResult && $classGenerated) {
            $this->restoreGeneratedFile($generatedClass);
        }

        $generatedServicesYaml = new GeneratedFile($servicesYamlFile, 'servicesYaml');
        $generatedServicesYaml->setGenerated($servicesYamlGenerated);
        if (\is_string($servicesYamlResult)) {
            $generatedServicesYaml->setContent($servicesYamlResult);
        }

        $result = new GeneratorResult();
        $result->addFile($generatedClass);
        $result->addFile($generatedServicesYaml);

        return $result;
    }

    /**
     * @return array{namespace: string, className: string}
     */
    public function resolveClassParts(string $extensionKey, string $name): array
    {
        return $this->classResolver->buildClassParts(
            $extensionKey,
            $this->classResolver->sanitizeNamespacePart($name),
            'Renderer\\Helper',
            'Helper'
        );
    }

    /**
     * @return array{0: string, 1: string|bool}
     */
    protected function writeToServicesYaml(
        string $extensionKey,
        string $name,
        string $className,
        string $methodName = Definition\HelperClassDefinition::DEFAULT_METHOD_NAME,
        bool $overwriteExistingFile = false
    ): array {
        $filename = GeneralUtility::getFileAbsFileName(sprintf('EXT:%s/Configuration/Services.yaml', $extensionKey));
        $content = [
            'services' => [
                $className => [
                    'tags' => [
                        [
                            'name' => 'handlebars.helper',
                            'identifier' => $name,
                            'method' => $methodName,
                        ],
                    ],
                ],
            ],
        ];

        if (empty($filename)) {
            throw new \RuntimeException(
                sprintf('Unable to determine path to Services.yaml of extension with key "%s"!', $extensionKey),
                1622139890
            );
        }

        if (!file_exists($filename)) {
            $overwriteExistingFile = true;
            $success = $this->yamlWriter->writeDefaultServicesYaml(
                $filename,
                $this->classResolver->resolveVendorNamespace($extensionKey)
            );
            if (!$success) {
                throw new \RuntimeException(
                    sprintf('Unable to create default Services.yaml file for EXT:%s', $extensionKey),
                    1622472830
                );
            }
        }

        $result = [
            $filename,
        ];

        if ($overwriteExistingFile) {
            $result[] = $this->yamlWriter->write($filename, $content);
        } else {
            $result[] = $this->yamlWriter->fill($filename, $content, true);
        }

        return $result;
    }
}
