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

namespace CPSIT\Typo3Handlebars\Exception;

/**
 * TypeIsNotSupported
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class TypeIsNotSupported extends Exception
{
    /**
     * @param non-empty-string|non-empty-list<non-empty-string> $expected
     */
    public function __construct(string|array $expected, mixed $actual)
    {
        $types = is_array($expected) ? $expected : [$expected];

        parent::__construct(
            sprintf(
                'The type "%s" is not supported, it must be %s"%s" instead.',
                get_debug_type($actual),
                count($types) > 1 ? 'one of ' : '',
                implode('", "', $types),
            ),
            1781515238,
        );
    }
}
