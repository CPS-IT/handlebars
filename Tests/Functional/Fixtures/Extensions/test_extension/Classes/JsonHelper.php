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

namespace Fr\Typo3Handlebars\TestExtension;

use DevTheorem\Handlebars;
use Fr\Typo3Handlebars\Attribute;
use Fr\Typo3Handlebars\Renderer;

/**
 * JsonHelper
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Attribute\AsHelper('jsonEncode')]
final class JsonHelper implements Renderer\Helper\Helper
{
    public function render(Handlebars\HelperOptions $options): Handlebars\SafeString
    {
        return new Handlebars\SafeString(json_encode($options->scope, JSON_THROW_ON_ERROR));
    }
}
