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

use Fr\Typo3Handlebars\Renderer;
use Psr\Log;

/**
 * ContentHelper
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @see https://github.com/shannonmoeller/handlebars-layouts#content-name-modeappendprependreplace
 */
class ContentHelper implements HelperInterface
{
    public function __construct(
        protected readonly Log\LoggerInterface $logger,
    ) {}

    /**
     * @param array<string, mixed> $options
     * @return string|bool
     */
    public function evaluate(string $name, array $options)
    {
        $data = $options['_this'];
        $mode = $options['hash']['mode'] ?? Renderer\Component\Layout\HandlebarsLayoutAction::REPLACE;
        $layoutStack = $this->getLayoutStack($options);

        // Early return if "content" helper is requested outside of an "extend" helper block
        if (empty($layoutStack)) {
            $this->logger->error('Handlebars layout helper "content" can only be used within an "extend" helper block!', ['name' => $name]);
            return '';
        }

        // Get upper layout from stack
        $layout = end($layoutStack);

        // Usage in conditional context: Test whether given required block is registered
        if (!\is_callable($options['fn'] ?? '')) {
            if (!$layout->isParsed()) {
                $layout->parse();
            }

            return $layout->hasAction($name);
        }

        // Add concrete action for the requested block
        $action = new Renderer\Component\Layout\HandlebarsLayoutAction($data, $options['fn'], $mode);
        $layout->addAction($name, $action);

        // This helper does not return any content, it's just here to register layout actions
        return '';
    }

    /**
     * @param array<string, mixed> $options
     * @return Renderer\Component\Layout\HandlebarsLayout[]
     */
    protected function getLayoutStack(array $options): array
    {
        // Fetch layout stack from current context
        if (isset($options['_this']['_layoutStack'])) {
            return $options['_this']['_layoutStack'];
        }

        // Early return if only context is currently processed
        if (!isset($options['contexts'])) {
            return [];
        }

        // Fetch layout stack from previous contexts
        while (!empty($options['contexts'])) {
            $context = array_pop($options['contexts']);
            if (isset($context['_layoutStack'])) {
                return $context['_layoutStack'];
            }
        }

        return [];
    }
}
