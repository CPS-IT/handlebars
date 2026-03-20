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
use TYPO3\CMS\Core;
use TYPO3\CMS\Frontend;

/**
 * SupportsDataSourceAwareProcessing
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
trait SupportsDataSourceAwareProcessing
{
    /**
     * @param array<string, mixed> $processorConfiguration
     * @param array<string|int, mixed> $variables
     * @return array<string|int, mixed>
     * @throws Exception\ConfiguredProcessorIsUnsupported
     */
    private function triggerDataSourceAwareProcessors(
        array $processorConfiguration,
        string $processorKey,
        array $variables,
        DataSourceCollection $collection,
        Frontend\ContentObject\ContentObjectRenderer $contentObjectRenderer,
    ): array {
        // Early return if no processors are registered
        if (!is_array($processorConfiguration[$processorKey . '.'] ?? null)) {
            return $variables;
        }

        ksort($processorConfiguration[$processorKey . '.']);

        /** @var string $processorClassName */
        foreach ($processorConfiguration[$processorKey . '.'] as $processorClassName) {
            if (!is_a($processorClassName, DataSourceAwareProcessor::class, true)) {
                throw new Exception\ConfiguredProcessorIsUnsupported($processorClassName);
            }

            $processor = Core\Utility\GeneralUtility::makeInstance($processorClassName);
            $variables = $processor->process($variables, $collection, $contentObjectRenderer);
        }

        return $variables;
    }
}
