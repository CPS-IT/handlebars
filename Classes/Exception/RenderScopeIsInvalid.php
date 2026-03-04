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
 * RenderScopeIsInvalid
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class RenderScopeIsInvalid extends Exception
{
    public function __construct(mixed $scope)
    {
        parent::__construct(
            sprintf('The render scope must be of type array, %s given.', get_debug_type($scope)),
            1758094413,
        );
    }
}
