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

use CPSIT\Typo3Handlebars\DataProcessing;

/**
 * PathIsMissingInDataSource
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class PathIsMissingInDataSource extends Exception
{
    public function __construct(
        public readonly string $path,
        public readonly DataProcessing\DataSource\DataSource $dataSource,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            sprintf('The request path "%s" is missing in data source "%s".', $this->path, $dataSource->value),
            1768386419,
            $previous,
        );
    }
}
