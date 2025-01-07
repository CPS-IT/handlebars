<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2024 Elias Häußler <e.haeussler@familie-redlich.de>
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

namespace Fr\Typo3Handlebars\Renderer\Component\Layout;

/**
 * HandlebarsLayout
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class HandlebarsLayout
{
    protected bool $parsed = false;

    /**
     * @var array<string, list<HandlebarsLayoutAction>>
     */
    protected array $actions = [];

    /**
     * @param callable $parseFunction
     */
    public function __construct(
        protected $parseFunction,
    ) {}

    public function parse(): void
    {
        $this->parsed = true;
        ($this->parseFunction)();
    }

    public function addAction(string $name, HandlebarsLayoutAction $action): void
    {
        if (!isset($this->actions[$name])) {
            $this->actions[$name] = [];
        }
        $this->actions[$name][] = $action;
    }

    /**
     * @return ($name is null ? array<string, HandlebarsLayoutAction[]> : HandlebarsLayoutAction[])
     */
    public function getActions(string $name = null): array
    {
        if ($name === null) {
            return $this->actions;
        }

        return $this->actions[$name] ?? [];
    }

    public function hasAction(string $name): bool
    {
        return \array_key_exists($name, $this->actions);
    }

    public function isParsed(): bool
    {
        return $this->parsed;
    }
}
