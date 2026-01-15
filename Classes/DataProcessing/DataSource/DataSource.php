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

namespace CPSIT\Typo3Handlebars\DataProcessing\DataSource;

/**
 * DataSource
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
enum DataSource: string
{
    case ContentObjectConfiguration = 'contentObjectConfiguration';
    case ContentObjectRenderer = 'contentObjectRenderer';
    case ProcessedData = 'processedData';
    case ProcessorConfiguration = 'processorConfiguration';

    public function getPriority(): int
    {
        return match ($this) {
            self::ContentObjectConfiguration => 0,
            self::ContentObjectRenderer => 1,
            self::ProcessedData => 2,
            self::ProcessorConfiguration => 3,
        };
    }

    /**
     * @param list<DataSource> $dataSources
     * @return list<DataSource>
     */
    public static function sortByPriority(array $dataSources): array
    {
        usort($dataSources, static fn(self $a, self $b) => ($a->getPriority() <=> $b->getPriority()) * -1);

        return $dataSources;
    }
}
