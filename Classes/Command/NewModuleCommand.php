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

use Fr\Typo3Handlebars\Generator\ModuleGenerator;
use Highlight\Decorator\StatefulCliDecorator;
use Highlight\Highlighter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Package\PackageManager;

/**
 * NewModuleCommand
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 *
 * @property ModuleGenerator $generator
 */
final class NewModuleCommand extends BaseFileGenerationCommand
{
    public function __construct(
        ModuleGenerator $generator,
        PackageManager $packageManager,
        FrontendInterface $diCache,
        string $name = null
    ) {
        parent::__construct($name);
        $this->generator = $generator;
        $this->packageManager = $packageManager;
        $this->diCache = $diCache;
        $this->highlighter = new Highlighter(new StatefulCliDecorator());
    }

    protected function configure(): void
    {
        $this->setDescription('Create a new Handlebars module');
        $this->addArgument(
            'name',
            InputArgument::REQUIRED,
            'Name of the new Handlebars module'
        );
        $this->addOption(
            'extension-key',
            'e',
            InputOption::VALUE_REQUIRED,
            'Extension key of the extension where to create the new module'
        );
        $this->addOption(
            'force-overwrite',
            null,
            InputOption::VALUE_NONE,
            'Force overwrite of existing files that need to be changed'
        );
        $this->addOption(
            'flush-cache',
            null,
            InputOption::VALUE_NONE,
            'Flush DI cache after successful file generation (only if Services.yaml is written)'
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $hasInteracted = false;
        $helper = $this->getHelper('question');

        // Ask for input argument "name"
        if (!$input->getArgument('name')) {
            $hasInteracted = true;
            $question = new Question('Name: ');
            $question->setValidator([$this, 'validateNameArgument']);
            $input->setArgument('name', $helper->ask($input, $output, $question));
        }

        // Ask for input option "extension-key"
        if (!$input->getOption('extension-key')) {
            $hasInteracted = true;
            $question = new Question('Extension key: ');
            $question->setValidator([$this, 'validateExtensionKeyOption']);
            $question->setAutocompleterValues($this->getAvailableExtensions());
            $input->setOption('extension-key', $helper->ask($input, $output, $question));
        }

        // Ask for input option "force-overwrite"
        if ($hasInteracted && !$input->getOption('force-overwrite')) {
            $question = new ConfirmationQuestion('Overwrite existing files? [<info>no</info>] ', false);
            $input->setOption('force-overwrite', $helper->ask($input, $output, $question));
        }

        // Ask for input option "flush"
        if ($hasInteracted && !$input->getOption('flush-cache')) {
            $question = new ConfirmationQuestion('Flush DI cache afterwards? [<info>no</info>] ', false);
            $input->setOption('flush-cache', $helper->ask($input, $output, $question));
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        // Result input options
        $name = $this->validateNameArgument($input->getArgument('name'));
        $this->extensionKey = $this->validateExtensionKeyOption($input->getOption('extension-key'));
        $forceOverwrite = $input->getOption('force-overwrite');
        $flushDiCache = $input->getOption('flush-cache');

        // Run module generation
        $generatorOptions = [
            'extensionKey' => $this->extensionKey,
        ];
        $result = $this->generator->generate($name, $generatorOptions, $forceOverwrite);
        $this->handleResult($result, $name, 'module', $flushDiCache);

        return 0;
    }
}
