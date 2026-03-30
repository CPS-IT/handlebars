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

/**
 * LookupHelper
 *
 * Overloads the default `lookup` implementation by additionally registering a potentially available partial.
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Attribute\AsHelper('lookup')]
final readonly class LookupHelper implements Helper
{
    public function __construct(
        private Renderer\Template\TemplateResolver $templateResolver,
    ) {}

    public function render(Handlebars\HelperOptions $options, mixed $subject = null, string|int|null $key = null): mixed
    {
        if ($subject === null || $key === null) {
            return null;
        }

        // Lookup variable using default helper implementation
        $function = Handlebars\Runtime::defaultHelpers()['lookup'];
        $result = $function($subject, $key);

        if (is_string($result)) {
            $this->tryPartialResolving($result, $options);
        }

        return $result;
    }

    private function tryPartialResolving(string $name, Handlebars\HelperOptions $options): void
    {
        // Don't load partial multiple times
        if ($options->hasPartial($name)) {
            return;
        }

        try {
            $path = $this->templateResolver->resolvePartialPath($name);
            $partial = @file_get_contents($path);
        } catch (\Exception) {
            // Exit if partial path could not be resolved. This can happen due to various reasons
            // (e.g. lookup must not always be used for partial path resolving) and is anyway
            // already handled upstream (Handlebars Runtime throws an exception on missing partial).
            return;
        }

        if ($partial !== false) {
            $options->registerPartial($name, Handlebars\Handlebars::compile($partial));
        }
    }
}
