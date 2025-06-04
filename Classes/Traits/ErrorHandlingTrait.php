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

namespace Fr\Typo3Handlebars\Traits;

use Psr\Log\LoggerInterface;

/**
 * ErrorHandlingTrait
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
trait ErrorHandlingTrait
{
    protected function handleError(\Throwable $error): void
    {
        if (property_exists($this, 'logger') && $this->logger instanceof LoggerInterface) {
            $this->logger->critical(
                'Data processing for ' . static::class . ' failed.',
                ['exception' => $error]
            );
        }
    }
}
