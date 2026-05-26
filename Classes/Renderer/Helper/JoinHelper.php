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
use DevTheorem\Handlebars;

/**
 * JoinHelper
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Attribute\AsHelper('join')]
final readonly class JoinHelper implements Helper
{
    public function render(Handlebars\HelperOptions $options, mixed ...$parts): string
    {
        $separator = $options->hash['separator'] ?? '';

        if (!is_string($separator)) {
            $separator = '';
        }

        return implode($separator, array_filter(array_map($this->convertToString(...), $parts), is_string(...)));
    }

    private function convertToString(mixed $value): ?string
    {
        if (is_scalar($value)) {
            return (string)$value;
        }

        if ($value instanceof \Stringable) {
            return (string)$value;
        }

        return null;
    }
}
