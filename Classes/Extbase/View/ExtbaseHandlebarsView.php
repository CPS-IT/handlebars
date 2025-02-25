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

namespace Fr\Typo3Handlebars\Extbase\View;

use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Frontend;
use TYPO3Fluid\Fluid;

/**
 * ExtbaseHandlebarsView
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class ExtbaseHandlebarsView implements Fluid\View\ViewInterface
{
    /**
     * @param array<string, mixed> $contentObjectConfiguration
     */
    public function __construct(
        private readonly Frontend\ContentObject\ContentObjectRenderer $contentObjectRenderer,
        private readonly Core\TypoScript\TypoScriptService $typoScriptService,
        private array $contentObjectConfiguration,
    ) {}

    public function assign(string $key, mixed $value): self
    {
        // Maintain TypoScript object structure
        if (\is_array($value)) {
            $key .= '.';
            $value = $this->typoScriptService->convertPlainArrayToTypoScriptArray($value);
        }

        $this->contentObjectConfiguration['variables.'][$key] = $value;

        return $this;
    }

    /**
     * @param array<string, mixed> $values
     */
    public function assignMultiple(array $values): self
    {
        foreach ($values as $key => $value) {
            $this->assign($key, $value);
        }

        return $this;
    }

    public function render(): string
    {
        return $this->contentObjectRenderer->cObjGetSingle('HANDLEBARSTEMPLATE', $this->contentObjectConfiguration);
    }

    /**
     * @param array<string, mixed> $variables
     */
    public function renderSection($sectionName, array $variables = [], $ignoreUnknown = false): string
    {
        // This is a Fluid feature, sections are not available in Handlebars.
        return '';
    }

    /**
     * @param array<string, mixed> $variables
     */
    public function renderPartial($partialName, $sectionName, array $variables, $ignoreUnknown = false): string
    {
        // This is a Fluid feature, Handlebars renderer takes care of rendering partials.
        return '';
    }

    public function setTemplateName(string $templateName): self
    {
        $this->contentObjectConfiguration['templateName'] = $templateName;

        return $this;
    }

    public function setTemplateNameFromRequest(Extbase\Mvc\RequestInterface $request): self
    {
        return $this->setTemplateName(
            $request->getControllerName() . '/' . $request->getControllerActionName(),
        );
    }
}
