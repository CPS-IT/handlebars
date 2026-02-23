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

namespace CPSIT\Typo3Handlebars\View;

use Psr\Http\Message;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Core;
use TYPO3\CMS\Frontend;

/**
 * HandlebarsView
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[DependencyInjection\Attribute\Exclude]
final class HandlebarsView implements Core\View\ViewInterface
{
    /**
     * @param array<string, mixed> $contentObjectConfiguration
     */
    public function __construct(
        private readonly Frontend\ContentObject\ContentObjectRenderer $contentObjectRenderer,
        private readonly Core\TypoScript\TypoScriptService $typoScriptService,
        private array $contentObjectConfiguration,
        private readonly ?Message\ServerRequestInterface $request = null,
    ) {}

    public function assign(string $key, mixed $value): self
    {
        // Maintain TypoScript object structure
        if (\is_array($value)) {
            $key .= '.';
            $value = $this->typoScriptService->convertPlainArrayToTypoScriptArray($value);
        }

        if ($key === 'settings.') {
            $this->contentObjectConfiguration['settings.'] ??= [];

            Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($this->contentObjectConfiguration['settings.'], $value);
        } else {
            $this->contentObjectConfiguration['variables.'][$key] = $value;
        }

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

    public function render(string $templateFileName = ''): string
    {
        $contentObjectRenderer = $this->contentObjectRenderer;
        $contentObjectConfiguration = $this->contentObjectConfiguration;

        if ($templateFileName !== '') {
            $contentObjectConfiguration['templateName'] = $templateFileName;
        }

        if ($this->request !== null) {
            $contentObjectRenderer = clone $contentObjectRenderer;
            $contentObjectRenderer->setRequest($this->request);
        }

        return $contentObjectRenderer->cObjGetSingle('HANDLEBARSTEMPLATE', $contentObjectConfiguration);
    }

    public function setTemplateName(string $templateName): self
    {
        $this->contentObjectConfiguration['templateName'] = $templateName;

        return $this;
    }

    public function getRequest(): ?Message\ServerRequestInterface
    {
        return $this->request;
    }
}
