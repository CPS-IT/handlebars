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

namespace CPSIT\Typo3Handlebars\Renderer\Helper;

use CPSIT\Typo3Handlebars\Attribute;
use CPSIT\Typo3Handlebars\Renderer;
use DevTheorem\Handlebars;
use Psr\Log;

/**
 * ContentHelper
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @see https://github.com/shannonmoeller/handlebars-layouts#content-name-modeappendprependreplace
 */
#[Attribute\AsHelper('content')]
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
