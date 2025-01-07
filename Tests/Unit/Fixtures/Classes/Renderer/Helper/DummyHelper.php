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

namespace Fr\Typo3Handlebars\Tests\Unit\Fixtures\Classes\Renderer\Helper;

use Fr\Typo3Handlebars\Renderer;

/**
 * DummyHelper
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 */
final readonly class DummyHelper implements Renderer\Helper\HelperInterface
{
    public function render(Renderer\Helper\Context\HelperContext $context): string
    {
        return 'foo';
    }

    public function __invoke(): string
    {
        return 'foo';
    }

    public static function staticExecute(): string
    {
        return 'foo';
    }

    public function execute(): string
    {
        return 'foo';
    }

    /* @phpstan-ignore method.unused */
    private function executeInternal(): string
    {
        return 'foo';
    }
}
