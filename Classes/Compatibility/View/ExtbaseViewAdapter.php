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
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\View\AbstractTemplateView;

/**
 * ExtbaseViewAdapter
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class ExtbaseViewAdapter extends AbstractTemplateView
{
    /**
     * @var array<string, mixed>
     */
    protected array $renderData = [];

    public function __construct(
        protected readonly DataProcessorInterface $processor,
    ) {
        parent::__construct();
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

    public function render($actionName = null): string
    {
        $renderingContext = $this->getRenderingContext();
        $controller = null;
        $request = null;

        if ($renderingContext instanceof RenderingContext) {
            $request = $renderingContext->getRequest();
            $actionName ??= $renderingContext->getControllerAction();
        }
        if ($request instanceof Request) {
            $controller = $request->getControllerObjectName();
        }

        return $this->processor->process('', [
            'extbaseViewConfiguration' => [
                'controller' => $controller,
                'action' => $actionName,
                'request' => $request,
                'variables' => $this->renderData,
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $variables
     */
    public function renderSection($sectionName, array $variables = [], $ignoreUnknown = false): string
    {
        return '';
    }

    /**
     * @param array<string, mixed> $variables
     */
    public function renderPartial($partialName, $sectionName = null, array $variables = [], $ignoreUnknown = false): string
    {
        return '';
    }
}
