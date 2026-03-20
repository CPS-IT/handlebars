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

namespace CPSIT\Typo3Handlebars\Tests;

use DevTheorem\Handlebars;

/**
 * HandlebarsTemplateTestTrait
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
trait HandlebarsTemplateTestTrait
{
    /**
     * @param array<string, mixed> $variables
     * @param array<string, callable> $helpers
     */
    public static function renderTemplate(string $template, array $variables = [], array $helpers = []): string
    {
        $options = new Handlebars\Options(
            helpers: array_map(
                static fn() => static fn() => '',
                $helpers,
            ),
        );
        $renderer = Handlebars\Handlebars::compile($template, $options);

        return $renderer($variables, [
            'helpers' => array_map(
                static fn(callable $fn) => static function () use ($fn) {
                    $arguments = func_get_args();
                    /** @var Handlebars\HelperOptions $options */
                    $options = array_pop($arguments);

                    return $fn($options, ...$arguments);
                },
                $helpers,
            ),
        ]);
    }

    /**
     * @param array<string, mixed> $variables
     * @param array<string, callable> $helpers
     */
    public static function assertRenderedTemplateEqualsString(
        string $template,
        string $expected,
        array $variables = [],
        array $helpers = [],
    ): void {
        $actual = self::renderTemplate($template, $variables, $helpers);

        self::assertSame(trim($expected), trim($actual));
    }
}
