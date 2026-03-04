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

namespace CPSIT\Typo3Handlebars\Tests\Functional\Fixtures\Classes;

use CPSIT\Typo3Handlebars\DataProcessing;
use TYPO3\CMS\Frontend;

/**
 * DataSourceCollectionManipulatingPreProcessor
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final readonly class DataSourceCollectionManipulatingPreProcessor implements DataProcessing\DataSource\DataSourceAwareProcessor
{
    public function process(
        array $variables,
        DataProcessing\DataSource\DataSourceCollection $collection,
        Frontend\ContentObject\ContentObjectRenderer $contentObjectRenderer,
    ): array {
        $dataSourceIdentifier = $collection->resolve(
            'dataSource',
            DataProcessing\DataSource\DataSource::ProcessorConfiguration,
        );
        $dataSource = DataProcessing\DataSource\DataSource::from($dataSourceIdentifier);
        $collection->remove($dataSource);

        return $variables;
    }
}
