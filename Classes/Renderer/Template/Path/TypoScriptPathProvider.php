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

namespace Fr\Typo3Handlebars\Renderer\Template\Path;

use Fr\Typo3Handlebars\Configuration;
use TYPO3\CMS\Extbase;

/**
 * TypoScriptPathProvider
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class TypoScriptPathProvider implements PathProvider
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $viewConfiguration = null;

    public function __construct(
        private readonly Extbase\Configuration\ConfigurationManagerInterface $configurationManager,
    ) {}

    public function getPartialRootPaths(): array
    {
        return $this->getViewConfiguration()[self::PARTIALS] ?? [];
    }

    public function getTemplateRootPaths(): array
    {
        return $this->getViewConfiguration()[self::TEMPLATES] ?? [];
    }

    public function isCacheable(): bool
    {
        return true;
    }

    /**
     * @return array{partialRootPaths?: array<int, string>, templateRootPaths?: array<int, string>}
     */
    private function getViewConfiguration(): array
    {
        if ($this->viewConfiguration === null) {
            $typoScriptConfiguration = $this->configurationManager->getConfiguration(
                Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
                Configuration\Extension::NAME,
            );
            $this->viewConfiguration = $typoScriptConfiguration['view'] ?? [];
        }

        return $this->viewConfiguration;
    }

    public static function getPriority(): int
    {
        return 50;
    }
}
