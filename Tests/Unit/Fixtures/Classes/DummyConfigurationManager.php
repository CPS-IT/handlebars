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

namespace Fr\Typo3Handlebars\Tests\Unit\Fixtures\Classes;

use TYPO3\CMS\Extbase;
use TYPO3\CMS\Frontend;

/**
 * DummyConfigurationManager
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 */
final class DummyConfigurationManager implements Extbase\Configuration\ConfigurationManagerInterface
{
    /**
     * @var array<string, mixed>
     */
    public array $configuration = [];

    private ?Frontend\ContentObject\ContentObjectRenderer $cObj = null;

    public function setContentObject(Frontend\ContentObject\ContentObjectRenderer $contentObject): void
    {
        $this->cObj = $contentObject;
    }

    public function getContentObject(): ?Frontend\ContentObject\ContentObjectRenderer
    {
        return $this->cObj;
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfiguration(string $configurationType, ?string $extensionName = null, ?string $pluginName = null): array
    {
        return $this->configuration;
    }

    /**
     * @param array<string, mixed> $configuration
     */
    public function setConfiguration(array $configuration = []): void
    {
        $this->configuration = $configuration;
    }

    public function isFeatureEnabled(string $featureName): bool
    {
        return false;
    }
}
