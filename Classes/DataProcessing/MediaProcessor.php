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
use TYPO3\CMS\Core;
use TYPO3\CMS\Frontend;

/**
 * Data processor to resolve a file resource and run it through a matching media processor.
 *
 * Example:
 * ========
 *
 * Given a "files" processor has resolved file references into "files", the first one
 * can be passed to the built-in "image" media processor to generate responsive source sets:
 *
 * tt_content.textpic = HANDLEBARSTEMPLATE
 * tt_content.textpic {
 *   dataProcessing {
 *     10 = files
 *     10 {
 *       references.fieldName = image
 *       as = files
 *     }
 *
 *     20 = media
 *     20 {
 *       file = processedData:files.0
 *       as = image
 *
 *       config {
 *         image {
 *           sourceSets {
 *             default {
 *               maxW = 600c
 *             }
 *           }
 *         }
 *       }
 *     }
 *   }
 * }
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[DependencyInjection\Attribute\AutoconfigureTag('data.processor', ['identifier' => 'media'])]
final readonly class MediaProcessor implements Frontend\ContentObject\DataProcessorInterface
{
    use DataSource\SupportsDataSource;
    use DataSource\SupportsDataSourceAwareProcessing;

    /**
     * @param iterable<string, Media\MediaProcessor> $mediaProcessors
     */
    public function __construct(
        private Log\LoggerInterface $logger,
        #[DependencyInjection\Attribute\AutowireIterator('handlebars.media_processor', 'key')]
        private iterable $mediaProcessors,
        private Core\TypoScript\TypoScriptService $typoScriptService,
        private DataSource\DataSourceProvider $dataSourceProvider,
    ) {}

    /**
     * @param array<string, mixed> $contentObjectConfiguration
     * @param array<string, mixed> $processorConfiguration
     * @param array<string, mixed> $processedData
     * @return array<string, mixed>
     */
    public function process(
        Frontend\ContentObject\ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData,
    ): array {
        $collection = DataSource\DataSourceCollection::for(
            $cObj,
            $contentObjectConfiguration,
            $processorConfiguration,
            $processedData,
        );

        $file = $this->provideData($cObj, $collection, 'file');

        // Early return if file cannot be resolved
        if ($file === null) {
            return $processedData;
        }

        /** @var string $as */
        $as = $collection->resolve('as', DataSource\DataSource::ProcessorConfiguration, 'result');
        $fileConfig = $collection->resolve('config.', DataSource\DataSource::ProcessorConfiguration, []);

        // Convert file config
        if (is_array($fileConfig) && $fileConfig !== []) {
            $fileConfig = $this->typoScriptService->convertTypoScriptArrayToPlainArray($fileConfig);
        } else {
            $fileConfig = [];
        }

        foreach ($this->mediaProcessors as $name => $mediaProcessor) {
            if ($mediaProcessor->supports($file)) {
                /** @var array<string, mixed>|null $mediaProcessorConfiguration */
                $mediaProcessorConfiguration = $fileConfig[$name] ?? null;
                $processedData[$as] = $mediaProcessor->process(
                    $cObj,
                    $file,
                    is_array($mediaProcessorConfiguration) ? $mediaProcessorConfiguration : [],
                );

                return $processedData;
            }
        }

        $this->logger->warning(
            'No suitable media processor found while processing {table}:{uid}.',
            [
                'table' => $cObj->getCurrentTable(),
                'uid' => $collection->resolveCurrentUid(),
            ],
        );

        return $processedData;
    }
}
