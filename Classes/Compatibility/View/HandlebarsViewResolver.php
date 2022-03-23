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

use Fr\Typo3Handlebars\DataProcessing\DataProcessorInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\View\GenericViewResolver;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

/**
 * HandlebarsViewResolver
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class HandlebarsViewResolver extends GenericViewResolver
{
    /**
     * @var array<string, array<string, DataProcessorInterface>>
     */
    protected $processorMap = [];

    public function resolve(string $controllerObjectName, string $actionName, string $format): ViewInterface
    {
        if (!$this->hasProcessor($controllerObjectName, $actionName)) {
            return parent::resolve($controllerObjectName, $actionName, $format);
        }

        return $this->buildView($controllerObjectName, $actionName);
    }

    protected function buildView(string $controllerClassName, string $actionName): ExtbaseViewAdapter
    {
        $processor = $this->getProcessor($controllerClassName, $actionName);

        return GeneralUtility::makeInstance(ExtbaseViewAdapter::class, $processor);
    }

    protected function hasProcessor(string $controllerClassName, string $actionName): bool
    {
        return null !== $this->getProcessor($controllerClassName, $actionName);
    }

    protected function getProcessor(string $controllerClassName, string $actionName): ?DataProcessorInterface
    {
        if (!\array_key_exists($controllerClassName, $this->processorMap)) {
            return null;
        }

        $processors = $this->processorMap[$controllerClassName];
        $fallbackProcessor = null;

        foreach ($processors as $action => $processor) {
            if ('_all' === $action) {
                $fallbackProcessor = $processor;
            } elseif ($actionName === $action) {
                return $processor;
            }
        }

        return $fallbackProcessor;
    }

    /**
     * @param array<string, array<string, DataProcessorInterface>> $processorMap
     * @return self
     */
    public function setProcessorMap(array $processorMap): self
    {
        $this->processorMap = $processorMap;
        return $this;
    }
}
