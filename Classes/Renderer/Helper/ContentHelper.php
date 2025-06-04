<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2024 Elias Häußler <e.haeussler@familie-redlich.de>
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

namespace Fr\Typo3Handlebars\Renderer\Helper;

use DevTheorem\Handlebars;
use Fr\Typo3Handlebars\Renderer;
use Psr\Log;

/**
 * ContentHelper
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @see https://github.com/shannonmoeller/handlebars-layouts#content-name-modeappendprependreplace
 */
final readonly class ContentHelper implements Helper
{
    private const DEFAULT_MODE = Renderer\Component\Layout\HandlebarsLayoutActionMode::Replace;

    public function __construct(
        private Renderer\Component\Layout\HandlebarsLayoutStack $layoutStack,
        private Log\LoggerInterface $logger,
    ) {}

    public function render(Handlebars\HelperOptions $options, string $name = ''): ?bool
    {
        $mode = $this->resolveLayoutActionMode($options, $name);

        // Early return if "content" helper is requested outside of an "extend" helper block
        if ($this->layoutStack->isEmpty()) {
            $this->logger->error(
                'Handlebars layout helper "content" can only be used within an "extend" helper block!',
                ['name' => $name],
            );

            return $options->fn() ? null : false;
        }

        // Get upper layout from stack
        $layout = $this->layoutStack->last();

        // Usage in conditional context: Test whether given required block is registered
        if (!$options->fn()) {
            if (!$layout->isParsed()) {
                $layout->parse();
            }

            return $layout->hasAction($name);
        }

        // Add concrete action for the requested block
        $action = new Renderer\Component\Layout\HandlebarsLayoutAction($name, $options, $mode);
        $layout->addAction($action);

        // This helper does not return any content, it's just here to register layout actions
        return null;
    }

    private function resolveLayoutActionMode(
        Handlebars\HelperOptions $options,
        string $name,
    ): Renderer\Component\Layout\HandlebarsLayoutActionMode {
        if (!isset($options->hash['mode'])) {
            return self::DEFAULT_MODE;
        }

        $mode = Renderer\Component\Layout\HandlebarsLayoutActionMode::tryFromCaseInsensitive($options->hash['mode']);

        if ($mode === null) {
            $mode = self::DEFAULT_MODE;

            $this->logger->warning(
                \sprintf(
                    'Handlebars layout helper "content" has invalid mode "%s". Falling back to "%s".',
                    $options->hash['mode'],
                    $mode->value,
                ),
                ['name' => $name],
            );
        }

        return $mode;
    }
}
