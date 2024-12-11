<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
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

namespace Fr\Typo3Handlebars\Tests;

use Fr\Typo3Handlebars\Renderer;

/**
 * HandlebarsTemplateResolverTrait
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
trait HandlebarsTemplateResolverTrait
{
    protected string $templateRootPath = __DIR__ . '/Unit/Fixtures/Templates';
    protected string $partialRootPath = __DIR__ . '/Unit/Fixtures/Partials';

    protected ?Renderer\Template\TemplateResolverInterface $templateResolver = null;
    protected ?Renderer\Template\TemplateResolverInterface $partialResolver = null;

    protected function getTemplateResolver(): Renderer\Template\TemplateResolverInterface
    {
        return $this->templateResolver ??= new Renderer\Template\HandlebarsTemplateResolver($this->getTemplatePaths());
    }

    protected function getPartialResolver(): Renderer\Template\TemplateResolverInterface
    {
        return $this->partialResolver ??= new Renderer\Template\HandlebarsTemplateResolver(
            $this->getTemplatePaths(Renderer\Template\TemplatePaths::PARTIALS),
        );
    }

    protected function getTemplatePaths(string $type = Renderer\Template\TemplatePaths::TEMPLATES): Unit\Fixtures\Classes\Renderer\Template\DummyTemplatePaths
    {
        return new Unit\Fixtures\Classes\Renderer\Template\DummyTemplatePaths(
            new Unit\Fixtures\Classes\DummyConfigurationManager(),
            $this->getViewConfiguration($type),
            $type,
        );
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function getViewConfiguration(string $type = Renderer\Template\TemplatePaths::TEMPLATES): array
    {
        $templateRootPath = match ($type) {
            Renderer\Template\TemplatePaths::PARTIALS => $this->partialRootPath,
            default => $this->templateRootPath,
        };

        return [
            $type => [10 => $templateRootPath],
        ];
    }
}
