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

namespace Fr\Typo3Handlebars\Event;

use Fr\Typo3Handlebars\Renderer;

/**
 * AfterRenderingEvent
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class AfterRenderingEvent
{
    public function __construct(
        private readonly Renderer\Template\View\HandlebarsView $view,
        private string $content,
        private readonly Renderer\Renderer $renderer,
    ) {}

    public function getView(): Renderer\Template\View\HandlebarsView
    {
        return $this->view;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getRenderer(): Renderer\Renderer
    {
        return $this->renderer;
    }
}
