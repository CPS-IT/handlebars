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

use Fr\Typo3Handlebars\DependencyInjection\HandlebarsRendererResolverTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * Console command "handlebars:list:helpers".
 *
 * Use this console command to show all globally registered Handlebars helpers. Note that
 * only helpers that are registered using the service configuration will be shown.
 *
 * Usage:
 *
 *   handlebars:list:helpers
 *
 * Example:
 *
 *   handlebars:list:helpers
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class ListHelpersCommand extends Command
{
    use HandlebarsRendererResolverTrait;

    private const TYPE_CLOSURE = 'closure';
    private const TYPE_FUNCTION = 'function';
    private const TYPE_METHOD = 'method';
    private const TYPE_UNKNOWN = 'unknown';

    /**
     * @var array[]
     */
    protected $registeredHelpers;

    /**
     * @param ServiceLocator $rendererLocator
     * @param string|null $name
     */
    public function __construct(ServiceLocator $rendererLocator, string $name = null)
    {
        parent::__construct($name);
        $this->registeredHelpers = $this->getHelpersOfRegisteredRenderers($rendererLocator, 'renderer');
        ksort($this->registeredHelpers);
    }

    protected function configure(): void
    {
        $this->setDescription('Print all registered helpers of Helper-aware renders');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Early return if no helpers are registered
        if ([] === $this->registeredHelpers) {
            $io->success('There are currently no Handlebars helpers registered.');

            return 0;
        }

        foreach ($this->registeredHelpers as $rendererServiceId => $helpers) {
            $rendererClassName = reset($helpers)['renderer'];

            $io->section(sprintf('Renderer: %s', $rendererServiceId));
            if ($rendererServiceId !== $rendererClassName) {
                $io->writeln([sprintf('Renderer class: <info>%s</info>', $rendererClassName), '']);
            }

            ksort($helpers);
            $io->table(['Name', 'Callable'], iterator_to_array($this->decorateHelpersForTable($helpers)));
        }

        return 0;
    }

    /**
     * @param array[] $helpers
     * @return \Generator<array>
     */
    private function decorateHelpersForTable(array $helpers): \Generator
    {
        foreach ($helpers as $name => ['callable' => $callable]) {
            yield ['<info>' . $name . '</info>', $this->decorateCallable($callable)];
        }
    }

    private function decorateCallable(callable $callable): string
    {
        switch ($this->determineCallableType($callable)) {
            case self::TYPE_CLOSURE:
                return 'Closure';

            case self::TYPE_FUNCTION:
                /* @phpstan-ignore-next-line */
                return (string)$callable;

            case self::TYPE_METHOD:
                [$classOrObject, $methodName] = $callable;
                if (is_object($classOrObject)) {
                    $classOrObject = get_class($classOrObject);
                }
                return '<fg=cyan>' . $classOrObject . '</>::' . $methodName;

            case self::TYPE_UNKNOWN:
            default:
                return '<error>Unknown</error>';
        }
    }

    private function determineCallableType(callable $callable): string
    {
        if ($callable instanceof \Closure) {
            return self::TYPE_CLOSURE;
        }

        if (is_string($callable)) {
            return self::TYPE_FUNCTION;
        }

        if (is_array($callable)) {
            return self::TYPE_METHOD;
        }

        return self::TYPE_UNKNOWN;
    }
}
