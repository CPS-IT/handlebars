<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2025 Elias Häußler <e.haeussler@familie-redlich.de>
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

namespace Fr\Typo3Handlebars\Renderer\Template\Path;

use Symfony\Component\DependencyInjection;

/**
 * GlobalPathProvider
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final readonly class GlobalPathProvider implements PathProvider
{
    /**
     * @var array<int, string>
     */
    private array $partialRootPaths;

    /**
     * @var array<int, string>
     */
    private array $templateRootPaths;

    /**
     * @param array{
     *     partialRootPaths: array<int, string>,
     *     templateRootPaths: array<int, string>,
     * } $viewConfiguration
     */
    public function __construct(
        #[DependencyInjection\Attribute\Autowire([
            self::PARTIALS => '%handlebars.partialRootPaths%',
            self::TEMPLATES => '%handlebars.templateRootPaths%',
        ])]
        array $viewConfiguration,
    ) {
        $this->partialRootPaths = $viewConfiguration[self::PARTIALS];
        $this->templateRootPaths = $viewConfiguration[self::TEMPLATES];
    }

    public function getPartialRootPaths(): array
    {
        return $this->partialRootPaths;
    }

    public function getTemplateRootPaths(): array
    {
        return $this->templateRootPaths;
    }

    public static function getPriority(): int
    {
        return 0;
    }
}
