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

namespace Fr\Typo3Handlebars\Tests\Unit\Fixtures\Classes\DataProcessing;

use Fr\Typo3Handlebars\Data;
use Fr\Typo3Handlebars\DataProcessing;
use Fr\Typo3Handlebars\Exception;
use Fr\Typo3Handlebars\Presenter;
use PHPUnit\Framework;
use TYPO3\CMS\Frontend;

/**
 * DummyProcessor
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 */
final class DummyProcessor extends DataProcessing\AbstractDataProcessor
{
    public bool $shouldThrowException = false;
    public bool $shouldInitializeConfigurationManager = false;

    protected function render(): string
    {
        if ($this->shouldThrowException) {
            throw new Exception\UnableToPresentException();
        }
        if ($this->shouldInitializeConfigurationManager) {
            $this->initializeConfigurationManager();
        }

        Framework\Assert::assertInstanceOf(Data\DataProvider::class, $this->provider);
        Framework\Assert::assertInstanceOf(Presenter\Presenter::class, $this->presenter);

        $content = $this->content . $this->presenter->present($this->provider->get([]));
        if ($this->configuration !== []) {
            $content .= ' ' . json_encode($this->configuration);
        }

        return $content;
    }

    /**
     * @impure
     */
    public function getContentObjectRenderer(): ?Frontend\ContentObject\ContentObjectRenderer
    {
        return $this->contentObjectRenderer;
    }
}
