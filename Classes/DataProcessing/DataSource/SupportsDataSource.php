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

use CPSIT\Typo3Handlebars\Exception;
use Psr\Log;
use TYPO3\CMS\Frontend;

/**
 * SupportsDataSource
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 *
 * @property DataSourceProvider $dataSourceProvider
 * @property Log\LoggerInterface $logger
 */
trait SupportsDataSource
{
    /**
     * @param non-empty-string $keyword
     */
    protected function provideData(
        Frontend\ContentObject\ContentObjectRenderer $contentObjectRenderer,
        DataSourceCollection $collection,
        string $keyword = 'dataSource',
    ): mixed {
        try {
            return $this->dataSourceProvider->provide($collection, $keyword);
        } catch (Exception\DataSourceIsMissingInCollection $exception) {
            $this->logger->warning(
                'No data provided for data source "{source}" while processing {table}:{uid}.',
                [
                    'source' => $exception->dataSource->value,
                    'table' => $contentObjectRenderer->getCurrentTable(),
                    'uid' => $collection->resolveCurrentUid(),
                ],
            );
        } catch (Exception\DataSourceIsNotSupported $exception) {
            $this->logger->warning(
                'Invalid data source keyword "{source}" passed while processing {table}:{uid}.',
                [
                    'source' => $exception->dataSourceIdentifier,
                    'table' => $contentObjectRenderer->getCurrentTable(),
                    'uid' => $collection->resolveCurrentUid(),
                ],
            );
        } catch (Exception\PathIsMissingInDataSource $exception) {
            $this->logger->warning(
                'Invalid path "{path}" for data source "{source}" passed while processing {table}:{uid}.',
                [
                    'path' => $exception->path,
                    'source' => $exception->dataSource->value,
                    'table' => $contentObjectRenderer->getCurrentTable(),
                    'uid' => $collection->resolveCurrentUid(),
                ],
            );
        }

        return null;
    }
}
