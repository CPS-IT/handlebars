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

namespace CPSIT\Typo3Handlebars\Controller;

use CPSIT\Typo3Handlebars\Exception;
use CPSIT\Typo3Handlebars\Extbase\View\ExtbaseHandlebarsView;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Extbase;

/**
 * HandlebarsController
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[DependencyInjection\Attribute\AutoconfigureTag('handlebars.extbase_controller')]
abstract class HandlebarsController extends Extbase\Mvc\Controller\ActionController
{
    /**
     * @throws Exception\ViewIsNotSupported
     */
    protected function renderView(?string $templateName = null): string
    {
        if (!($this->view instanceof ExtbaseHandlebarsView)) {
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
