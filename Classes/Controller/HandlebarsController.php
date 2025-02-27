<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2025 Elias Häußler <e.haeussler@familie-redlich.de>
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

namespace Fr\Typo3Handlebars\Controller;

use Fr\Typo3Handlebars\Exception;
use Fr\Typo3Handlebars\Extbase\View;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Extbase;
use TYPO3Fluid\Fluid;

/**
 * HandlebarsController
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
abstract class HandlebarsController extends Extbase\Mvc\Controller\ActionController
{
    protected View\ExtbaseHandlebarsViewResolver $viewResolver;

    public function injectViewResolver(
        #[DependencyInjection\Attribute\Autowire(service: View\ExtbaseHandlebarsViewResolver::class)]
        Extbase\Mvc\View\ViewResolverInterface $viewResolver,
    ): void {
        parent::injectViewResolver($viewResolver);
    }

    protected function resolveView(): Fluid\View\ViewInterface
    {
        $baseView = parent::resolveView();

        if (!($baseView instanceof Fluid\View\AbstractTemplateView)) {
            return $baseView;
        }

        /** @var View\ExtbaseHandlebarsView $view */
        $view = $this->viewResolver->resolve(
            $this->request->getControllerObjectName(),
            $this->request->getControllerActionName(),
            $this->request->getFormat(),
            false,
        );
        $view->assignMultiple((array)$baseView->getRenderingContext()->getVariableProvider()->getAll());
        $view->setTemplateNameFromRequest($this->request);

        return $view;
    }

    protected function renderView(?string $templateName = null): string
    {
        if (!($this->view instanceof View\ExtbaseHandlebarsView)) {
            throw new Exception\ViewIsNotSupported($this->view);
        }

        if ($templateName !== null) {
            $view = clone $this->view;
            $view->setTemplateName($templateName);
        } else {
            $view = $this->view;
        }

        return $view->render();
    }
}
