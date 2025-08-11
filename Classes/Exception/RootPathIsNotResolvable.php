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
 * RootPathIsNotResolvable
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class RootPathIsNotResolvable extends Exception
{
    public function __construct(string $rootPath)
    {
        parent::__construct(
            \sprintf('Root path "%s" is not resolvable (either not allowed or not existing).', $rootPath),
            1736257279,
        );
    }
}
