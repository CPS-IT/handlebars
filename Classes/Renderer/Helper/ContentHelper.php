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

use Fr\Typo3Handlebars\Attribute;
use Fr\Typo3Handlebars\Renderer;
use Psr\Log;

/**
 * ContentHelper
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @see https://github.com/shannonmoeller/handlebars-layouts#content-name-modeappendprependreplace
 */
#[Attribute\AsHelper('content')]
final readonly class ContentHelper implements HelperInterface
{
    private const DEFAULT_MODE = Renderer\Component\Layout\HandlebarsLayoutActionMode::Replace;

    public function __construct(
        private Log\LoggerInterface $logger,
    ) {}

    public function render(Context\HelperContext $context): ?bool
    {
        $name = $context[0];
        $mode = $this->resolveLayoutActionMode($context, $name);
        $layoutStack = $this->getLayoutStack($context);

        // Early return if "content" helper is requested outside of an "extend" helper block
        if (empty($layoutStack)) {
            $this->logger->error(
                'Handlebars layout helper "content" can only be used within an "extend" helper block!',
                ['name' => $name],
            );

            return $context->isBlockHelper() ? null : false;
        }

        // Get upper layout from stack
        $layout = end($layoutStack);

        // Usage in conditional context: Test whether given required block is registered
        if (!$context->isBlockHelper()) {
            if (!$layout->isParsed()) {
                $layout->parse();
            }

            return $layout->hasAction($name);
        }

        // Add concrete action for the requested block
        $action = new Renderer\Component\Layout\HandlebarsLayoutAction($name, $context, $mode);
        $layout->addAction($action);

        // This helper does not return any content, it's just here to register layout actions
        return null;
    }

    /**
     * @return Renderer\Component\Layout\HandlebarsLayout[]
     */
    private function getLayoutStack(Context\HelperContext $context): array
    {
        $renderingContext = $context->renderingContext;
        $contextStack = $context->contextStack;

        // Fetch layout stack from current context
        if (isset($renderingContext['_layoutStack'])) {
            return $renderingContext['_layoutStack'];
        }

        // Fetch layout stack from previous contexts
        while (!$contextStack->isEmpty()) {
            $currentContext = $contextStack->pop();

            if (isset($currentContext['_layoutStack'])) {
                return $currentContext['_layoutStack'];
            }
        }

        return [];
    }

    private function resolveLayoutActionMode(
        Context\HelperContext $context,
        string $name,
    ): Renderer\Component\Layout\HandlebarsLayoutActionMode {
        if (!isset($context['mode'])) {
            return self::DEFAULT_MODE;
        }

        $mode = Renderer\Component\Layout\HandlebarsLayoutActionMode::tryFromCaseInsensitive($context['mode']);

        if ($mode === null) {
            $mode = self::DEFAULT_MODE;

            $this->logger->warning(
                \sprintf(
                    'Handlebars layout helper "content" has invalid mode "%s". Falling back to "%s".',
                    $context['mode'],
                    $mode->value,
                ),
                ['name' => $name],
            );
        }

        return $mode;
    }
}
