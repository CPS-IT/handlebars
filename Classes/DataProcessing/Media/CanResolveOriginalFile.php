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

namespace CPSIT\Typo3Handlebars\DataProcessing\Media;

use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;

/**
 * CanResolveOriginalFile
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
trait CanResolveOriginalFile
{
    protected function resolveOriginalFile(mixed $resource): ?Core\Resource\AbstractFile
    {
        if ($resource instanceof Extbase\Domain\Model\File || $resource instanceof Extbase\Domain\Model\FileReference) {
            $resource = $resource->getOriginalResource();
        }

        if ($resource instanceof Core\Resource\FileReference) {
            $resource = $resource->getOriginalFile();
        }

        if ($resource instanceof Core\Resource\AbstractFile) {
            return $resource;
        }

        return null;
    }
}
