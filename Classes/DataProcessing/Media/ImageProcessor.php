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

use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Frontend;

/**
 * ImageProcessor
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 *
 * @extends ConfigurableProcessor<Configuration\ImageConfiguration>
 *
 * @phpstan-type SourceSet array{src: string, width: int, height: int, extension: string, processedFile: Core\Resource\ProcessedFile|null}
 */
#[DependencyInjection\Attribute\AsTaggedItem('image')]
final class ImageProcessor extends ConfigurableProcessor
{
    use CanResolveOriginalFile;

    /**
     * @return array{
     *     sourceSets: array<string, SourceSet>,
     *     originalFile: Core\Resource\AbstractFile,
     * }
     */
    public function processFile(
        Frontend\ContentObject\ContentObjectRenderer $contentObjectRenderer,
        Core\Resource\ResourceInterface|Extbase\Domain\Model\File|Extbase\Domain\Model\FileReference $resource,
        Configuration\Configuration $configuration,
    ): array {
        /** @var Core\Resource\AbstractFile $file */
        $file = $this->resolveOriginalFile($resource);

        return [
            'sourceSets' => $this->processSourceSets($contentObjectRenderer, $resource, $configuration->sourceSets),
            'originalFile' => $file,
        ];
    }

    /**
     * @param array<string, array<string, string|int>> $sourceSets
     * @return array<string, SourceSet>
     */
    private function processSourceSets(
        Frontend\ContentObject\ContentObjectRenderer $contentObjectRenderer,
        Core\Resource\ResourceInterface|Extbase\Domain\Model\File|Extbase\Domain\Model\FileReference $resource,
        array $sourceSets,
    ): array {
        // Pull original resources from extbase models
        if ($resource instanceof Extbase\Domain\Model\FileReference || $resource instanceof Extbase\Domain\Model\File) {
            $resource = $resource->getOriginalResource();
        }

        // Early return on invalid resource
        if (!($resource instanceof Core\Resource\File) && !($resource instanceof Core\Resource\FileReference)) {
            return [];
        }

        $processedSourceSets = [];

        foreach ($sourceSets as $name => $sourceSet) {
            $processedSourceSet = $contentObjectRenderer->getImgResource($resource, $sourceSet);
            $publicUrl = $processedSourceSet?->getPublicUrl();

            if ($publicUrl !== null) {
                $processedSourceSets[$name] = [
                    'src' => $publicUrl,
                    'width' => $processedSourceSet->getWidth(),
                    'height' => $processedSourceSet->getHeight(),
                    'extension' => $processedSourceSet->getExtension(),
                    'processedFile' => $processedSourceSet->getProcessedFile(),
                ];
            }
        }

        return $processedSourceSets;
    }

    /**
     * @phpstan-assert-if-true !null $this->resolveOriginalFile()
     */
    public function supports(mixed $resource): bool
    {
        return $this->resolveOriginalFile($resource)?->isImage() ?? false;
    }

    protected function getConfigurationClass(): string
    {
        return Configuration\ImageConfiguration::class;
    }
}
