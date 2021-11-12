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

namespace Fr\Typo3Handlebars\Tests\Unit\Fixtures\Classes;

use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * DummyConfigurationManager
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 */
final class DummyConfigurationManager implements ConfigurationManagerInterface
{
    /**
     * @var array<string, mixed>
     */
    public $configuration = [];

    /**
     * @var ContentObjectRenderer
     */
    private $cObj;

    public function setContentObject(ContentObjectRenderer $contentObject): void
    {
        $this->cObj = $contentObject;
    }

    public function getContentObject(): ?ContentObjectRenderer
    {
        return $this->cObj;
    }

    /**
     * @inheritDoc
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
