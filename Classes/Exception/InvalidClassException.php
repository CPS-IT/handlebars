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

namespace Fr\Typo3Handlebars\Exception;

/**
 * InvalidClassException
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class InvalidClassException extends \RuntimeException
{
    /**
     * @param class-string $className
     */
    public static function create(string $className): self
    {
        return new self(\sprintf('The class "%s" does not exist.', $className), 1638182580);
    }

    public static function forService(string $serviceId): self
    {
        return new self(
            \sprintf('Class name of service "%s" cannot be resolved or does not exist.', $serviceId),
            1638183576
        );
    }
}
