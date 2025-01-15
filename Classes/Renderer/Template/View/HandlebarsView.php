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

namespace Fr\Typo3Handlebars\Renderer\Template\View;

use Fr\Typo3Handlebars\Exception;
use Fr\Typo3Handlebars\Renderer;

/**
 * HandlebarsView
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class HandlebarsView
{
    private ?string $templateSource = null;
    private ?string $format = null;

    /**
     * @param array<string, mixed> $variables
     */
    public function __construct(
        private ?string $templatePath = null,
        private array $variables = [],
    ) {}

    /**
     * @throws Exception\TemplateFileIsInvalid
     * @throws Exception\TemplateFormatIsNotSupported
     * @throws Exception\TemplatePathIsNotResolvable
     * @throws Exception\ViewIsNotProperlyInitialized
     */
    public function getTemplate(?Renderer\Template\TemplateResolver $templateResolver = null): string
    {
        if ($this->templateSource !== null) {
            return $this->templateSource;
        }

        if ($this->templatePath === null) {
            throw new Exception\ViewIsNotProperlyInitialized();
        }

        if ($templateResolver !== null) {
            $fullTemplatePath = $templateResolver->resolveTemplatePath($this->templatePath, $this->format);
        } else {
            $format = $this->format !== null ? '.' . $this->format : '';
            $fullTemplatePath = $this->templatePath . $format;
        }

        return $this->fetchFromFile($fullTemplatePath);
    }

    /**
     * @throws Exception\TemplateFileIsInvalid
     */
    private function fetchFromFile(string $file): string
    {
        if (!\is_file($file)) {
            throw new Exception\TemplateFileIsInvalid($file);
        }

        $template = @file_get_contents($file);

        if ($template === false) {
            throw new Exception\TemplateFileIsInvalid($file);
        }

        return $template;
    }

    public function setTemplatePath(string $templatePath): self
    {
        $this->templatePath = $templatePath;

        return $this;
    }

    public function setTemplateSource(string $templateSource): self
    {
        $this->templateSource = $templateSource;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    public function assign(string $key, mixed $value): self
    {
        $this->variables[$key] = $value;

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

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(string $format): self
    {
        $this->format = $format;

        return $this;
    }
}
