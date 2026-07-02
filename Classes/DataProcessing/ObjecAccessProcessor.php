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

namespace CPSIT\Typo3Handlebars\DataProcessing;

use Psr\Log;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Frontend;

/**
 * Data processor to access a given object by a given property path.
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[DependencyInjection\Attribute\AutoconfigureTag('data.processor', ['identifier' => 'object-access'])]
final readonly class ObjecAccessProcessor implements Frontend\ContentObject\DataProcessorInterface
{
    public function __construct(
        private Log\LoggerInterface $logger,
        private Frontend\ContentObject\ContentDataProcessor $contentDataProcessor,
    ) {}

    /**
     * @param array<string, mixed> $contentObjectConfiguration
     * @param array<string, mixed> $processorConfiguration
     * @param array<string|int, mixed> $processedData
     * @return array<string|int, mixed>
     * @throws Extbase\Reflection\Exception\PropertyNotAccessibleException
     * @throws Frontend\ContentObject\Exception\ContentRenderingException
     */
    public function process(
        Frontend\ContentObject\ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData,
    ): array {
        $collection = new DataSource\DataSourceCollection();
        $collection->set(DataSource\DataSource::ContentObjectRenderer, $cObj->data);
        $collection->set(DataSource\DataSource::ContentObjectConfiguration, $contentObjectConfiguration);
        $collection->set(DataSource\DataSource::ProcessedData, $processedData);
        $collection->set(DataSource\DataSource::ProcessorConfiguration, $processorConfiguration);

        $objectSource = $collection->resolve('object', DataSource\DataSource::ProcessorConfiguration);

        // Early return if object source is not configured
        if (!is_string($objectSource) || $objectSource === '') {
            $this->logger->warning(
                'Invalid object source configured for "object-access" data processor while processing {table}:{uid}.',
                [
                    'table' => $cObj->getCurrentTable(),
                    'uid' => $collection->resolveCurrentUid(),
                ],
            );

            return $processedData;
        }

        /** @var string $as */
        $as = $collection->resolve('as', DataSource\DataSource::ProcessorConfiguration, 'result');
        $path = $collection->resolve('path', DataSource\DataSource::ProcessorConfiguration);
        $object = $collection->resolve($objectSource);

        // Early return if either object or path is not valid
        if (!is_string($path) || !is_object($object)) {
            $this->logger->warning(
                'Invalid object or path configured for "object-access" data processor while processing {table}:{uid}.',
                [
                    'table' => $cObj->getCurrentTable(),
                    'uid' => $collection->resolveCurrentUid(),
                ],
            );

            return $processedData;
        }

        // Early return if object path is not gettable
        if (!Extbase\Reflection\ObjectAccess::isPropertyGettable($object, $path)) {
            $this->logger->warning(
                'Configured object path "{path}" is not gettable for object at "{objectSource}" while processing {table}:{uid}.',
                [
                    'path' => $path,
                    'objectSource' => $objectSource,
                    'table' => $cObj->getCurrentTable(),
                    'uid' => $collection->resolveCurrentUid(),
                ],
            );

            return $processedData;
        }

        // Resolve property
        $processedData[$as] = Extbase\Reflection\ObjectAccess::getProperty($object, $path);

        // Process additional data processors
        if (is_array($processedData[$as])) {
            $processedData[$as] = $this->contentDataProcessor->process($cObj, $processorConfiguration, $processedData[$as]);
        }

        return $processedData;
    }
}
