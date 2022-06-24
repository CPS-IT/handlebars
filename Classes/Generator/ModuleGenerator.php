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

use Fr\Typo3Handlebars\Exception\ConstraintViolationException;
use Fr\Typo3Handlebars\Generator\Definition\DataProcessorClassDefinition;
use Fr\Typo3Handlebars\Generator\Definition\DataProviderClassDefinition;
use Fr\Typo3Handlebars\Generator\Definition\PresenterClassDefinition;
use Fr\Typo3Handlebars\Generator\Definition\ProviderResponseClassDefinition;
use Fr\Typo3Handlebars\Generator\Resolver\ClassResolver;
use Fr\Typo3Handlebars\Generator\Result\GeneratedFile;
use Fr\Typo3Handlebars\Generator\Result\GeneratorResult;
use Fr\Typo3Handlebars\Generator\Writer\PhpWriter;
use Fr\Typo3Handlebars\Generator\Writer\YamlWriter;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ModuleGenerator
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class ModuleGenerator implements GeneratorInterface
{
    use FileGenerationTrait;

    /**
     * @var string
     */
    protected $providerResponseTemplateName = 'ProviderResponse.php.tpl';

    /**
     * @var string
     */
    protected $providerTemplateName = 'DataProvider.php.tpl';

    /**
     * @var string
     */
    protected $presenterTemplateName = 'Presenter.php.tpl';

    /**
     * @var string
     */
    protected $processorTemplateName = 'DataProcessor.php.tpl';

    /**
     * @var YamlWriter
     */
    protected $yamlWriter;

    /**
     * @var string
     */
    protected $extensionKey;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $overwriteFiles = false;

    /**
     * @var GeneratorResult
     */
    protected $result;

    public function __construct(
        PhpWriter $phpWriter,
        YamlWriter $yamlWriter,
        ClassResolver $classResolver
    ) {
        $this->phpWriter = $phpWriter;
        $this->yamlWriter = $yamlWriter;
        $this->classResolver = $classResolver;
    }

    public function generate(string $name, array $options, bool $overwriteExistingFiles = false): GeneratorResult
    {
        $this->extensionKey = $options['extensionKey'] ?? null;
        $this->name = $name;
        $this->overwriteFiles = $overwriteExistingFiles;

        if (empty($this->extensionKey) || !ExtensionManagementUtility::isLoaded($this->extensionKey)) {
            throw ConstraintViolationException::createForUnsupportedExtension($this->extensionKey);
        }

        $this->result = new GeneratorResult();

        try {
            $this->generateProviderResponseClass();
            $this->generateProviderClass();
            $this->generatePresenterClass();
            $this->generateProcessorClass();
        } catch (\Exception $exception) {
            $this->restoreGeneratedFiles();

            /** @noinspection PhpUnhandledExceptionInspection */
            throw $exception;
        }

        $processor = $this->result->getByType('processorClass');
        [$servicesYamlFile, $servicesYamlResult] = $this->writeToServicesYaml(
            $this->extensionKey,
            $processor['className'],
            $this->overwriteFiles
        );
        $servicesYamlGenerated = true === $servicesYamlResult;

        // Remove generated classes if Services.yaml could not be written
        if (false === $servicesYamlResult) {
            $this->restoreGeneratedFiles();
        }

        $generatedServicesYaml = new GeneratedFile($servicesYamlFile, 'servicesYaml');
        $generatedServicesYaml->setGenerated($servicesYamlGenerated);
        if (\is_string($servicesYamlResult)) {
            $generatedServicesYaml->setContent($servicesYamlResult);
        }
        $this->result->addFile($generatedServicesYaml);

        return $this->result;
    }

    protected function generateProviderResponseClass(): void
    {
        // Resolve class parts
        $classParts = $this->classResolver->buildClassParts(
            $this->extensionKey,
            $this->classResolver->sanitizeNamespacePart($this->name),
            'Data\\Response',
            'ProviderResponse'
        );

        // Build class definition
        $classDefinitionBuilder = new ProviderResponseClassDefinition();
        $classDefinition = $classDefinitionBuilder->build($this->name);

        // Generate class
        $this->addClassResult('providerResponseClass', $classParts, $classDefinition);
    }

    protected function generateProviderClass(): void
    {
        // Resolve class parts
        $classParts = $this->classResolver->buildClassParts(
            $this->extensionKey,
            $this->classResolver->sanitizeNamespacePart($this->name),
            'Data',
            'Provider'
        );

        // Build class definition
        $providerResponseClass = $this->result->getByType('providerResponseClass');
        $classDefinitionBuilder = new DataProviderClassDefinition();
        $classDefinition = $classDefinitionBuilder->build($this->name, [
            'providerResponseClassName' => '\\' . $providerResponseClass['className'],
        ]);

        $this->addClassResult('providerClass', $classParts, $classDefinition);
    }

    protected function generatePresenterClass(): void
    {
        // Resolve class parts
        $classParts = $this->classResolver->buildClassParts(
            $this->extensionKey,
            $this->classResolver->sanitizeNamespacePart($this->name),
            'Presenter',
            'Presenter'
        );

        // Build class definition
        $providerResponseClass = $this->result->getByType('providerResponseClass');
        $classDefinitionBuilder = new PresenterClassDefinition();
        $classDefinition = $classDefinitionBuilder->build($this->name, [
            'providerResponseClassName' => '\\' . $providerResponseClass['className'],
            'timestamp' => time(),
        ]);

        $this->addClassResult('presenterClass', $classParts, $classDefinition);
    }

    protected function generateProcessorClass(): void
    {
        // Resolve class parts
        $classParts = $this->classResolver->buildClassParts(
            $this->extensionKey,
            $this->classResolver->sanitizeNamespacePart($this->name),
            'DataProcessing',
            'Processor'
        );

        // Build class definition
        $providerClass = $this->result->getByType('providerClass');
        $presenterClass = $this->result->getByType('presenterClass');
        $classDefinitionBuilder = new DataProcessorClassDefinition();
        $classDefinition = $classDefinitionBuilder->build($this->name, [
            'providerClassName' => '\\' . $providerClass['className'],
            'presenterClassName' => '\\' . $presenterClass['className'],
        ]);

        $this->addClassResult('processorClass', $classParts, $classDefinition);
    }

    /**
     * @param array{namespace: string, className: string} $classParts
     * @param array<string, mixed> $classDefinition
     */
    protected function addClassResult(string $type, array $classParts, array $classDefinition): void
    {
        [$className, $filename, $templateResult, $previousContent] = $this->generateClass(
            $this->extensionKey,
            $classParts,
            $classDefinition,
            $this->overwriteFiles
        );

        $file = new GeneratedFile($filename, $type, ['className' => $className]);
        $file->setGenerated(true === $templateResult);

        if (\is_string($templateResult)) {
            $file->setContent($templateResult);
        }

        if (null !== $previousContent) {
            $file->setPreviousContent($previousContent);
        }

        $this->result->addFile($file);
    }

    /**
     * @return array{0: string, 1: string|bool}
     */
    protected function writeToServicesYaml(
        string $extensionKey,
        string $className,
        bool $overwriteExistingFile = false
    ): array {
        $filename = GeneralUtility::getFileAbsFileName(sprintf('EXT:%s/Configuration/Services.yaml', $extensionKey));
        $content = [
            'services' => [
                $className => [
                    'tags' => [
                        [
                            'name' => 'handlebars.processor',
                        ],
                    ],
                ],
            ],
        ];

        if (empty($filename)) {
            throw new \RuntimeException(
                sprintf('Unable to determine path to Services.yaml of extension with key "%s"!', $extensionKey),
                1627584382
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
                    1627584392
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

    protected function restoreGeneratedFiles(): void
    {
        foreach ($this->result->getFiles() as $file) {
            if (!$file->isGenerated()) {
                continue;
            }

            $this->restoreGeneratedFile($file);
        }
    }
}
