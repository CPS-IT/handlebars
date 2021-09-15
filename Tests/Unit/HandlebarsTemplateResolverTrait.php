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

namespace Fr\Typo3Handlebars\Tests\Unit;

use Fr\Typo3Handlebars\Renderer\Template\HandlebarsTemplateResolver;
use Fr\Typo3Handlebars\Renderer\Template\TemplatePaths;
use Fr\Typo3Handlebars\Renderer\Template\TemplateResolverInterface;
use Fr\Typo3Handlebars\Tests\Unit\Fixtures\Classes\DummyConfigurationManager;
use Fr\Typo3Handlebars\Tests\Unit\Fixtures\Classes\Renderer\Template\DummyTemplatePaths;
use Symfony\Component\DependencyInjection\Container;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * HandlebarsTemplateResolverTrait
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
trait HandlebarsTemplateResolverTrait
{
    /**
     * @var string
     */
    protected $templateRootPath;

    /**
     * @var string
     */
    protected $partialRootPath;

    /**
     * @var TemplateResolverInterface
     */
    protected $templateResolver;

    /**
     * @var TemplateResolverInterface
     */
    protected $partialResolver;

    protected function getTemplateResolver(): TemplateResolverInterface
    {
        if ($this->templateResolver === null) {
            $this->templateResolver = new HandlebarsTemplateResolver($this->getTemplatePaths());
        }
        return $this->templateResolver;
    }

    protected function getPartialResolver(): TemplateResolverInterface
    {
        if ($this->partialResolver === null) {
            $this->partialResolver = new HandlebarsTemplateResolver($this->getTemplatePaths(TemplatePaths::PARTIALS));
        }
        return $this->partialResolver;
    }

    protected function getTemplatePaths(string $type = TemplatePaths::TEMPLATES): DummyTemplatePaths
    {
        $container = $this->getContainer($type);

        $templatePaths = new DummyTemplatePaths(new DummyConfigurationManager(), $type);
        $templatePaths->setContainer($container);

        return $templatePaths;
    }

    protected function getContainer(string $type = TemplatePaths::TEMPLATES): Container
    {
        $templateRootPath = TemplatePaths::PARTIALS === $type ? $this->getPartialRootPath() : $this->getTemplateRootPath();
        $container = new Container();
        $container->setParameter('handlebars.' . $type, [10 => $templateRootPath]);

        return $container;
    }

    public function getTemplateRootPath(): string
    {
        if ($this->templateRootPath === null) {
            $this->templateRootPath = GeneralUtility::getFileAbsFileName('EXT:handlebars/Tests/Unit/Fixtures/Templates');
        }
        return $this->templateRootPath;
    }

    public function getPartialRootPath(): string
    {
        if ($this->partialRootPath === null) {
            $this->partialRootPath = GeneralUtility::getFileAbsFileName('EXT:handlebars/Tests/Unit/Fixtures/Partials');
        }
        return $this->partialRootPath;
    }
}
