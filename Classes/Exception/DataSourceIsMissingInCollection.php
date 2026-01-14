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
 * DataSourceIsMissingInCollection
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class DataSourceIsMissingInCollection extends Exception
{
    public function __construct(
        public readonly DataProcessing\DataSource\DataSource $dataSource,
    ) {
        parent::__construct(
            sprintf('No data available for data source "%s" in collection.', $dataSource->value),
            1768386157,
        );
    }
}
