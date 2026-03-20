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

namespace CPSIT\Typo3Handlebars\Tests;

use CPSIT\Typo3Handlebars\Renderer;

/**
 * HandlebarsTemplateResolverTrait
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 */
trait HandlebarsTemplateResolverTrait
{
    protected string $templateRootPath = __DIR__ . '/Unit/Fixtures/Templates';
    protected string $partialRootPath = __DIR__ . '/Unit/Fixtures/Partials';

    protected ?Renderer\Template\TemplateResolver $templateResolver = null;

    protected function getTemplateResolver(): Renderer\Template\TemplateResolver
    {
        return $this->templateResolver ??= new Renderer\Template\HandlebarsTemplateResolver($this->getTemplatePaths());
    }

    protected function getTemplatePaths(): Renderer\Template\TemplatePaths
    {
        return new Renderer\Template\TemplatePaths([
            new Renderer\Template\Path\GlobalPathProvider($this->getViewConfiguration()),
        ]);
    }

    /**
     * @return array{partialRootPaths: array<int, string>, templateRootPaths: array<int, string>}
     */
    protected function getViewConfiguration(): array
    {
        return [
            Renderer\Template\Path\PathProvider::PARTIALS => [10 => $this->partialRootPath],
            Renderer\Template\Path\PathProvider::TEMPLATES => [10 => $this->templateRootPath],
        ];
    }

    protected function allowAdditionalRootPaths(): void
    {
        /* @phpstan-ignore property.notFound */
        $this->configurationToUseInTestInstance = [
            'BE' => [
                'lockRootPath' => [
                    $this->partialRootPath,
                    $this->templateRootPath,
                    dirname($this->partialRootPath),
                    dirname($this->templateRootPath),
                ],
            ],
        ];
    }
}
