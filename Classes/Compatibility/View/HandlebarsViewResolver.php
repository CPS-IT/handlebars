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

namespace Fr\Typo3Handlebars\Compatibility\View;

use Fr\Typo3Handlebars\DataProcessing;
use Psr\Http\Message;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use TYPO3Fluid\Fluid;

/**
 * HandlebarsViewResolver
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[DependencyInjection\Attribute\Autoconfigure(public: true)]
final class HandlebarsViewResolver extends Extbase\Mvc\View\GenericViewResolver
{
    /**
     * @var array<class-string, array<string, DataProcessing\DataProcessor>>
     */
    private array $processorMap = [];

    public function resolve(string $controllerObjectName, string $actionName, string $format): Fluid\View\ViewInterface
    {
        if (!$this->hasProcessor($controllerObjectName, $actionName)) {
            return parent::resolve($controllerObjectName, $actionName, $format);
        }

        return $this->buildView($controllerObjectName, $actionName);
    }

    private function buildView(string $controllerClassName, string $actionName): ExtbaseViewAdapter
    {
        $processor = $this->getProcessor($controllerClassName, $actionName);

        return Core\Utility\GeneralUtility::makeInstance(ExtbaseViewAdapter::class, $processor);
    }

    private function hasProcessor(string $controllerClassName, string $actionName): bool
    {
        return $this->getProcessor($controllerClassName, $actionName) !== null;
    }

    private function getProcessor(string $controllerClassName, string $actionName): ?DataProcessing\DataProcessor
    {
        if (!\array_key_exists($controllerClassName, $this->processorMap)) {
            return null;
        }

        $processors = $this->processorMap[$controllerClassName];
        $processor = $processors[$actionName] ?? $processors['_all'] ?? null;

        if ($processor === null) {
            return null;
        }

        $contentObjectRenderer = $this->getRequest()->getAttribute('currentContentObject');
        if ($contentObjectRenderer !== null && method_exists($processor, 'setContentObjectRenderer')) {
            $processor->setContentObjectRenderer($contentObjectRenderer);
        }

        return $processor;
    }

    /**
     * @param array<class-string, array<string, DataProcessing\DataProcessor>> $processorMap
     */
    public function setProcessorMap(array $processorMap): self
    {
        $this->processorMap = $processorMap;
        return $this;
    }

    private function getRequest(): Message\ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }
}
