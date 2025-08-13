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

namespace CPSIT\Typo3Handlebars\Renderer;

use CPSIT\Typo3Handlebars\Exception;
use CPSIT\Typo3Handlebars\Renderer;

/**
 * RenderingContext
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class RenderingContext
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
