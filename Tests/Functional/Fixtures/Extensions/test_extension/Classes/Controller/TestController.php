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

namespace Fr\Typo3Handlebars\TestExtension\Controller;

use Fr\Typo3Handlebars\Controller;
use Psr\Http\Message;

/**
 * TestController
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class TestController extends Controller\HandlebarsController
{
    public function defaultTemplateAction(): Message\ResponseInterface
    {
        $this->view->assign('name', 'Foo');

        return $this->htmlResponse();
    }

    public function renderedTemplateAction(): Message\ResponseInterface
    {
        $this->view->assign('name', 'Foo');

        return $this->htmlResponse(
            $this->renderView(),
        );
    }

    public function specificTemplateAction(): Message\ResponseInterface
    {
        $this->view->assign('renderedContent', 'Hello World!');

        return $this->htmlResponse(
            $this->renderView('foo'),
        );
    }
}
