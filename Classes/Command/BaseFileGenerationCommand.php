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

namespace Fr\Typo3Handlebars\Command;

use Fr\Typo3Handlebars\Generator\GeneratorInterface;
use Fr\Typo3Handlebars\Generator\Result\GeneratedFile;
use Fr\Typo3Handlebars\Generator\Result\GeneratorResult;
use Highlight\Highlighter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\DependencyInjection\Cache\ContainerBackend;
use TYPO3\CMS\Core\Package\PackageManager;

/**
 * BaseFileGenerationCommand
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
abstract class BaseFileGenerationCommand extends Command
{
    /**
     * @var GeneratorInterface
     */
    protected $generator;

    /**
     * @var PackageManager
     */
    protected $packageManager;

    /**
     * @var FrontendInterface
     */
    protected $diCache;

    /**
     * @var Highlighter
     */
    protected $highlighter;

    /**
     * @var string
     */
    protected $extensionKey;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    protected function handleResult(GeneratorResult $result, string $name, string $type, bool $flushDiCache = false): void
    {
        // Get results
        $allFiles = $result->getFiles();
        $generatedFiles = $result->getByFilter(function (GeneratedFile $file): bool {
            return $file->isGenerated();
        });
        $untouchedFiles = $result->getByFilter(function (GeneratedFile $file): bool {
            return !$file->isGenerated();
        });

        // Determine parts to be shown
        $showGeneratedFiles = true;
        $showTemplateResults = false;
        switch ($result->getState()) {
            case GeneratorResult::STATE_FAILED:
                $showGeneratedFiles = false;
                break;
            case GeneratorResult::STATE_INCOMPLETE:
                $showTemplateResults = true;
                break;
        }

        // Generated files
        if ($showGeneratedFiles) {
            $this->io->success('The following file(s) have been written:');
            $this->io->listing($this->decorateGeneratedFiles($generatedFiles, $this->extensionKey));
        }

        // Template results (= files, that need to be rewritten manually)
        if ($showTemplateResults) {
            $this->io->warning('The following file(s) must be rewritten manually:');
            $this->printUntouchedFiles($untouchedFiles);
        }

        if ($flushDiCache) {
            // Flush DI cache
            if ($this->servicesYamlWasWritten($allFiles) && $this->flushDiCache()) {
                $this->io->success('Successfully flushed the service container.');
            } else {
                $this->io->comment('Skipped cache flush since <comment>Services.yaml</comment> has not changed.');
            }
        } elseif ($showGeneratedFiles || $showTemplateResults) {
            // Show notice regarding outdated service container
            $this->io->warning(
                sprintf('You must re-create the service container in order to use the new Handlebars %s!', $type)
            );
        }

        // Show result state
        switch ($result->getState()) {
            case GeneratorResult::STATE_SUCCESSFUL:
                $this->io->success(sprintf('A new Handlebars %s "%s" has been created.', $type, $name));
                break;
            case GeneratorResult::STATE_INCOMPLETE:
                $this->io->warning(sprintf('A new Handlebars %s "%s" has been partially created, but some file(s) already exist and were not overwritten.', $type, $name));
                $this->io->writeln(['Tip: You can use <comment>--force-overwrite</comment> to overwrite existing files.', '']);
                break;
            case GeneratorResult::STATE_FAILED:
                $this->io->error(sprintf('The Handlebars %s "%s" already exists and could not be created.', $type, $name));
                break;
        }
    }

    protected function decorateFilename(string $filename, string $extensionKey): string
    {
        $packagePath = $this->packageManager->getPackage($extensionKey)->getPackagePath();

        if (strpos($filename, $packagePath) !== 0) {
            return $filename;
        }

        return sprintf(
            'EXT:%s%s%s',
            $extensionKey,
            DIRECTORY_SEPARATOR,
            trim(substr($filename, strlen($packagePath)), DIRECTORY_SEPARATOR)
        );
    }

    /**
     * @param GeneratedFile[] $files
     * @param string $extensionKey
     * @return string[]
     */
    protected function decorateGeneratedFiles(array $files, string $extensionKey): array
    {
        $decoratedFiles = [];

        foreach ($files as $file) {
            $decoratedFiles[] = sprintf(
                '<comment>%s</comment>: %s',
                basename($file->getFilename()),
                $this->decorateFilename($file->getFilename(), $extensionKey)
            );
        }

        return $decoratedFiles;
    }

    /**
     * @param GeneratedFile[] $files
     */
    protected function printUntouchedFiles(array $files): void
    {
        foreach ($files as $file) {
            $language = 'servicesYaml' === $file->getType() ? 'yaml' : 'php';
            $this->io->section($this->decorateFilename($file->getFilename(), $this->extensionKey));
            $this->io->writeln($this->highlighter->highlight($language, $file->getContent())->value);
        }
    }

    /**
     * @param GeneratedFile[] $files
     * @return bool
     */
    protected function servicesYamlWasWritten(array $files): bool
    {
        foreach ($files as $file) {
            if ('servicesYaml' === $file->getType()) {
                return $file->isGenerated();
            }
        }

        return false;
    }

    protected function flushDiCache(): bool
    {
        $cacheBackend = $this->diCache->getBackend();
        if (!($cacheBackend instanceof ContainerBackend)) {
            return false;
        }

        $cacheBackend->forceFlush();
        return true;
    }

    /**
     * @return \Generator<string>
     */
    protected function getAvailableExtensions(): \Generator
    {
        foreach ($this->packageManager->getActivePackages() as $package) {
            if (!$package->isProtected()) {
                yield $package->getPackageKey();
            }
        }
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function validateNameArgument($value): string
    {
        if (!is_string($value) || '' === trim($value)) {
            throw new \RuntimeException('The helper name cannot be empty.', 1622465202);
        }

        return trim($value);
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function validateExtensionKeyOption($value): string
    {
        if (!is_string($value) || '' === trim($value)) {
            throw new \RuntimeException('The extension key cannot be empty.', 1622465793);
        }
        if (!$this->packageManager->isPackageActive($value)) {
            throw new \RuntimeException('The given extension is either not active or protected.', 1622465853);
        }

        return $value;
    }
}
