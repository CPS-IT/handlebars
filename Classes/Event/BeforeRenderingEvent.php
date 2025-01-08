<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars_components".
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

namespace Fr\Typo3Handlebars\Event;

use Fr\Typo3Handlebars\Renderer;

/**
 * BeforeRenderingEvent
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class BeforeRenderingEvent
{
    /**
     * @param array<string|int, mixed> $variables
     */
    public function __construct(
        private readonly Renderer\Template\View\HandlebarsView $view,
        private array $variables,
        private readonly Renderer\Renderer $renderer,
    ) {}

    public function getView(): Renderer\Template\View\HandlebarsView
    {
        return $this->view;
    }

    /**
     * @return array<string|int, mixed>
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * @param array<string|int, mixed> $variables
     */
    public function setVariables(array $variables): self
    {
        $this->variables = $variables;
        return $this;
    }

    public function addVariable(string $name, mixed $value): self
    {
        $this->variables[$name] = $value;
        return $this;
    }

    public function removeVariable(string $name): self
    {
        unset($this->variables[$name]);
        return $this;
    }

    public function getRenderer(): Renderer\Renderer
    {
        return $this->renderer;
    }
}
