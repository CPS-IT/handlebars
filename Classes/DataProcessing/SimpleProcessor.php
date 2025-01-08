<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2020 Elias Häußler <e.haeussler@familie-redlich.de>
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

namespace Fr\Typo3Handlebars\DataProcessing;

use Fr\Typo3Handlebars\Exception\InvalidTemplateFileException;
use Fr\Typo3Handlebars\Renderer\Renderer;
use Fr\Typo3Handlebars\Traits\ErrorHandlingTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * SimpleProcessor
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Autoconfigure(public: true)]
class SimpleProcessor implements DataProcessor
{
    use ErrorHandlingTrait;

    protected ?ContentObjectRenderer $contentObjectRenderer = null;

    public function __construct(
        protected readonly LoggerInterface $logger,
        protected readonly Renderer $renderer,
    ) {}

    public function process(string $content, array $configuration): string
    {
        try {
            $templatePath = $this->getTemplatePath($configuration);
            return $this->renderer->render($templatePath, $this->contentObjectRenderer?->data ?? []);
        } catch (InvalidTemplateFileException $exception) {
            $this->handleError($exception);
            return '';
        }
    }

    /**
     * @param array<string|int, mixed> $configuration
     * @throws InvalidTemplateFileException
     */
    protected function getTemplatePath(array $configuration): string
    {
        if (
            !\array_key_exists('templatePath', $configuration['userFunc.'] ?? []) ||
            !\is_string($configuration['userFunc.']['templatePath']) ||
            trim($configuration['userFunc.']['templatePath']) === ''
        ) {
            throw new InvalidTemplateFileException(
                'Missing or invalid template path in configuration array.',
                1606834398
            );
        }
        return trim($configuration['userFunc.']['templatePath']);
    }

    public function setContentObjectRenderer(ContentObjectRenderer $contentObjectRenderer): self
    {
        $this->contentObjectRenderer = $contentObjectRenderer;
        return $this;
    }
}
