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

namespace Fr\Typo3Handlebars\TestExtension\Controller;

use Fr\Typo3Handlebars\Controller;
use Psr\Http\Message;

/**
 * TestController
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
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
