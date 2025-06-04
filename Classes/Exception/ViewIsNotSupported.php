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

use TYPO3\CMS\Core;
use TYPO3Fluid\Fluid;

/**
 * ViewIsNotSupported
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class ViewIsNotSupported extends Exception
{
    public function __construct(Fluid\View\ViewInterface|Core\View\ViewInterface $view)
    {
        parent::__construct(
            sprintf('The given view "%s" cannot be used to render Handlebars templates.', $view::class),
            1740478079,
        );
    }
}
