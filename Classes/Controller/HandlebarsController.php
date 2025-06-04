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
