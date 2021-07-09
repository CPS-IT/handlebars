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

use Fr\Typo3Handlebars\Data\DataProviderInterface;
use Fr\Typo3Handlebars\Exception\UnableToPresentException;
use Fr\Typo3Handlebars\Presenter\PresenterInterface;
use Fr\Typo3Handlebars\Traits\ErrorHandlingTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * AbstractDataProcessor
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
abstract class AbstractDataProcessor implements DataProcessorInterface, LoggerAwareInterface
{
    use ErrorHandlingTrait;
    use LoggerAwareTrait;

    /**
     * @var ContentObjectRenderer
     */
    public $cObj;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var array<string|int, mixed>
     */
    protected $configuration;

    /**
     * @var PresenterInterface
     */
    protected $presenter;

    /**
     * @var DataProviderInterface
     */
    protected $provider;

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
     * @param PresenterInterface $presenter
     * @return DataProcessorInterface
     */
    public function setPresenter(PresenterInterface $presenter): DataProcessorInterface
    {
        $this->presenter = $presenter;
        return $this;
    }

    /**
     * @required
     * @param DataProviderInterface $provider
     * @return DataProcessorInterface
     */
    public function setProvider(DataProviderInterface $provider): DataProcessorInterface
    {
        $this->provider = $provider;
        return $this;
    }

    /**
     * Process and render data.
     *
     * @return string The rendered data
     * @throws UnableToPresentException if data cannot be presented with this data processor
     */
    abstract protected function render(): string;
}
