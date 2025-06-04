<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Fr\Typo3Handlebars\Compatibility\View;

use Fr\Typo3Handlebars\DataProcessing;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Fluid;

/**
 * ExtbaseViewAdapter
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class ExtbaseViewAdapter extends Fluid\View\AbstractTemplateView
{
    /**
     * @var array<string, mixed>
     */
    private array $renderData = [];

    public function __construct(
        private readonly DataProcessing\DataProcessor $processor,
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

        if ($renderingContext instanceof Fluid\Core\Rendering\RenderingContext) {
            $request = $renderingContext->getRequest();
            $actionName ??= $renderingContext->getControllerAction();
        }
        if ($request instanceof Extbase\Mvc\Request) {
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
