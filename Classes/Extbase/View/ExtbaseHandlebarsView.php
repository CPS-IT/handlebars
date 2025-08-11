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

namespace CPSIT\Typo3Handlebars\Extbase\View;

use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Core;
use TYPO3\CMS\Frontend;

/**
 * ExtbaseHandlebarsView
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[DependencyInjection\Attribute\Exclude]
final class ExtbaseHandlebarsView implements Core\View\ViewInterface
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

    public function render(string $templateFileName = ''): string
    {
        $contentObjectConfiguration = $this->contentObjectConfiguration;

        if ($templateFileName !== '') {
            $contentObjectConfiguration['templateName'] = $templateFileName;
        }

        return $this->contentObjectRenderer->cObjGetSingle('HANDLEBARSTEMPLATE', $contentObjectConfiguration);
    }

    public function setTemplateName(string $templateName): self
    {
        $this->contentObjectConfiguration['templateName'] = $templateName;

        return $this;
    }
}
