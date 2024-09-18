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

use Fr\Typo3Handlebars\Renderer\HandlebarsRenderer;

/**
 * BeforeRenderingEvent
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class BeforeRenderingEvent
{
    /**
     * @param array<string|int, mixed> $data
     */
    public function __construct(
        private readonly string $templatePath,
        private array $data,
        private readonly HandlebarsRenderer $renderer,
    ) {}

    public function getTemplatePath(): string
    {
        return $this->templatePath;
    }

    /**
     * @return array<string|int, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array<string|int, mixed> $data
     */
    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function getRenderer(): HandlebarsRenderer
    {
        return $this->renderer;
    }
}
