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
use TYPO3\CMS\Core;

/**
 * VarDumpHelper
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Attribute\AsHelper('varDump')]
final readonly class VarDumpHelper implements Helper
{
    public function render(Handlebars\HelperOptions $options): Handlebars\SafeString
    {
        \ob_start();

        Core\Utility\DebugUtility::debug($options->scope);

        return new Handlebars\SafeString((string)\ob_get_clean());
    }
}
