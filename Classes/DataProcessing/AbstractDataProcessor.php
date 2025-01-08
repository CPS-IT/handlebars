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

use Fr\Typo3Handlebars\Data\DataProvider;
use Fr\Typo3Handlebars\Exception\UnableToPresentException;
use Fr\Typo3Handlebars\Presenter\Presenter;
use Fr\Typo3Handlebars\Traits\ErrorHandlingTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Core\Bootstrap;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * AbstractDataProcessor
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
abstract class AbstractDataProcessor implements DataProcessor, LoggerAwareInterface
{
    use ErrorHandlingTrait;
    use LoggerAwareTrait;

    protected ?ConfigurationManagerInterface $configurationManager = null;
    protected ?ContentObjectRenderer $contentObjectRenderer = null;
    protected ?Presenter $presenter = null;
    protected ?DataProvider $provider = null;
    protected string $content = '';

    /**
     * @var array<string|int, mixed>
     */
    protected array $configuration = [];

    /**
     * @todo Move to constructor with next BC break
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager): void
    {
        $this->configurationManager = $configurationManager;
    }

    public function process(string $content, array $configuration): string
    {
        $this->content = $content;
        $this->configuration = $configuration;

        try {
            return $this->render();
        } catch (UnableToPresentException $exception) {
            $this->handleError($exception);
            return '';
        }
    }

    /**
     * @required
     */
    public function setPresenter(Presenter $presenter): DataProcessor
    {
        $this->presenter = $presenter;
        return $this;
    }

    /**
     * @required
     */
    public function setProvider(DataProvider $provider): DataProcessor
    {
        $this->provider = $provider;
        return $this;
    }

    public function setContentObjectRenderer(ContentObjectRenderer $contentObjectRenderer): self
    {
        $this->contentObjectRenderer = $contentObjectRenderer;
        return $this;
    }

    /**
     * Make configuration manager stateless by resetting individual settings.
     *
     * Resets the extension name and plugin name applied to the configuration manager.
     * This is required in order to fully respect "pages" and "recursive" configuration
     * from the content object data in Extbase repositories. By default, this is handled
     * by the controller context in an action controller. Since we're outside of Extbase
     * context, we need to apply/reset those states by or own.
     *
     * @see Bootstrap::initializeConfiguration()
     */
    protected function initializeConfigurationManager(): void
    {
        if ($this->configurationManager !== null && $this->contentObjectRenderer !== null) {
            $fullConfiguration = $this->configurationManager->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
            );
            // setConfiguration() resets extensionName and pluginName
            $this->configurationManager->setConfiguration($fullConfiguration);
            $this->configurationManager->setContentObject($this->contentObjectRenderer);
        }
    }

    /**
     * Process and render data.
     *
     * @return string The rendered data
     * @throws UnableToPresentException if data cannot be presented with this data processor
     */
    abstract protected function render(): string;
}
