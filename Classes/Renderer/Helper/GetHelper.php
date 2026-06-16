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
use CPSIT\Typo3Handlebars\Exception;
use DevTheorem\Handlebars;
use TYPO3\CMS\Extbase;

/**
 * GetHelper
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Attribute\AsHelper('get')]
final readonly class GetHelper implements Helper
{
    /**
     * @throws Exception\TypeIsNotSupported
     * @throws Extbase\Reflection\Exception\PropertyNotAccessibleException
     */
    public function render(Handlebars\HelperOptions $options, mixed $subject = null, ?string $name = null): mixed
    {
        if (!is_object($subject) && !is_array($subject)) {
            throw new Exception\TypeIsNotSupported(['object', 'array'], $subject);
        }

        if (!is_string($name)) {
            throw new Exception\TypeIsNotSupported('string', $name);
        }

        return Extbase\Reflection\ObjectAccess::getProperty($subject, $name);
    }
}
