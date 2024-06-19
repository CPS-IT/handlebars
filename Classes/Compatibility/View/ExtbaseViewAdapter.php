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
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3Fluid\Fluid\View\ViewInterface;

/**
 * ExtbaseViewAdapter
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class ExtbaseViewAdapter implements ViewInterface
{
    /**
     * @var DataProcessorInterface
     */
    protected $processor;

    /**
     * @var array<string, mixed>
     */
    protected $renderData = [];

    public function __construct(DataProcessorInterface $processor)
    {
        $this->processor = $processor;
    }

    public function assign($key, $value): self
    {
        $this->renderData[$key] = $value;
        return $this;
    }

    /**
     * @param array<string, mixed> $values
     */
    public function assignMultiple(array $values): self
    {
        foreach ($values as $key => $value) {
            $this->assign($key, $value);
        }

        return $this;
    }

    public function canRender(): bool
    {
        return true;
    }

    public function render(): string
    {
        $renderingContext = GeneralUtility::makeInstance(RenderingContextFactory::class)->create();
        $request = $renderingContext->getRequest();

        return $this->processor->process('', [
            'extbaseViewConfiguration' => [
                'controller' => $request->getControllerObjectName(),
                'action' => $request->getControllerActionName(),
                'request' => $request,
                'variables' => $this->renderData,
            ],
        ]);
    }

    public function renderSection($sectionName, array $variables = [], $ignoreUnknown = false): string
    {
        return '';
    }

    public function renderPartial($partialName, $sectionName, array $variables, $ignoreUnknown = false): string
    {
        return '';
    }
}
