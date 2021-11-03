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

use Fr\Typo3Handlebars\DataProcessing\DataProcessorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command "handlebars:list:modules".
 *
 * Use this console command to show all globally registered Handlebars modules.
 * Note that only modules that are registered using the service configuration
 * will be shown.
 *
 * Usage:
 *
 *   handlebars:list:modules [name]
 *
 * Example:
 *
 *   handlebars:list:modules
 *   handlebars:list:modules TextMedia
 *   handlebars:list:modules "Form*"
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class ListModulesCommand extends Command
{
    /**
     * @var DataProcessorInterface[]
     */
    private $dataProcessors;

    /**
     * @var array<string, string>
     */
    private $resolvedBaseNames = [];

    /**
     * @inheritDoc
     * @param iterable<DataProcessorInterface> $dataProcessors
     */
    public function __construct(iterable $dataProcessors, string $name = null)
    {
        parent::__construct($name);

        $this->dataProcessors = $dataProcessors instanceof \Traversable ? iterator_to_array($dataProcessors) : $dataProcessors;
        $this->sortProcessorsByBaseName();
    }

    protected function configure(): void
    {
        $this->setDescription('Print all globally registered Handlebars modules');
        $this->addArgument(
            'name',
            InputArgument::OPTIONAL,
            'Optional name or glob of a concrete Handlebars module to be looked up'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('name');

        // Get all data processors or filter by given name
        if ($name !== null) {
            $dataProcessors = iterator_to_array($this->filterDataProcessorsByName($name));
        } else {
            $dataProcessors = $this->dataProcessors;
        }

        // Early return if no modules are available
        if ($dataProcessors === []) {
            if ($name !== null) {
                $io->error(sprintf('There is no Handlebars module registered with the name "%s".', $name));
                return 1;
            }

            $io->success('There are currently no Handlebars modules registered.');
            return 0;
        }

        // Print registered modules
        if ($io->isVerbose()) {
            foreach ($dataProcessors as $dataProcessor) {
                $baseName = $this->resolveBaseName($dataProcessor);
                $io->section($baseName);
                $io->listing(iterator_to_array($this->resolveDataProcessorMetadata($dataProcessor)));
            }
        } else {
            $io->table(
                ['Name', 'Declaring class'],
                iterator_to_array($this->decorateDataProcessorsForTable($dataProcessors))
            );
        }

        return 0;
    }

    private function resolveBaseName(DataProcessorInterface $dataProcessor): string
    {
        $className = get_class($dataProcessor);
        $namespacePart = '\\DataProcessing\\';

        // Get base name from cache
        if (array_key_exists($className, $this->resolvedBaseNames)) {
            return $this->resolvedBaseNames[$className];
        }

        // Early return if data processor is not within the required namespace
        if (($pos = strpos($className, $namespacePart)) === false) {
            return $className;
        }

        // Resolve base name
        return $this->resolvedBaseNames[$className] = substr($className, $pos + strlen($namespacePart), -9);
    }

    /**
     * @param DataProcessorInterface $dataProcessor
     * @return \Generator<string>
     */
    private function resolveDataProcessorMetadata(DataProcessorInterface $dataProcessor): \Generator
    {
        $reflection = new \ReflectionObject($dataProcessor);
        $template = '%s: <info>%s</info>';

        // Show data processor
        yield sprintf($template, 'DataProcessor', get_class($dataProcessor));

        // Show data provider
        if ($reflection->hasProperty('provider')) {
            $providerProperty = $reflection->getProperty('provider');
            $providerProperty->setAccessible(true);
            yield sprintf($template, 'DataProvider', get_class($providerProperty->getValue($dataProcessor)));
        }

        // Show presenter
        if ($reflection->hasProperty('presenter')) {
            $presenterProperty = $reflection->getProperty('presenter');
            $presenterProperty->setAccessible(true);
            yield sprintf($template, 'Presenter', get_class($presenterProperty->getValue($dataProcessor)));
        }
    }

    /**
     * @param DataProcessorInterface[] $dataProcessors
     * @return \Generator<array>
     */
    private function decorateDataProcessorsForTable(array $dataProcessors): \Generator
    {
        foreach ($dataProcessors as $dataProcessor) {
            yield [
                sprintf('<info>%s</info>', $this->resolveBaseName($dataProcessor)),
                get_class($dataProcessor),
            ];
        }
    }

    /**
     * @param string $name
     * @return \Generator<DataProcessorInterface>
     */
    private function filterDataProcessorsByName(string $name): \Generator
    {
        $lowercasedName = strtolower($name);

        foreach ($this->dataProcessors as $dataProcessor) {
            if (strtolower(get_class($dataProcessor)) === $lowercasedName) {
                yield $dataProcessor;
                continue;
            }

            $baseName = strtolower($this->resolveBaseName($dataProcessor));
            if (fnmatch($lowercasedName, $baseName) || $baseName === $lowercasedName . 'processor') {
                yield $dataProcessor;
            }
        }
    }

    private function sortProcessorsByBaseName(): void
    {
        usort($this->dataProcessors, function (DataProcessorInterface $a, DataProcessorInterface $b) {
            return strcmp($this->resolveBaseName($a), $this->resolveBaseName($b));
        });
    }
}
