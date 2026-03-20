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

use CPSIT\Typo3Handlebars\Renderer;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Frontend;

/**
 * Data processor to resolve and replace markers within configured variables.
 *
 * Example:
 * ========
 *
 * lib {
 *   navigation {
 *     template = @nav
 *     navData {
 *       items = ###NAV_ITEMS###
 *     }
 *   }
 * }
 *
 * page {
 *   10 = HANDLEBARSTEMPLATE
 *   10 {
 *     templateName = @page
 *     variables {
 *       mainNav < lib.navigation
 *     }
 *
 *     dataProcessing {
 *       10 = menu
 *       10.as = ###NAV_ITEMS###
 *
 *       90 = resolve-markers
 *       90 {
 *         removeNonMatchingMarkers = 1
 *       }
 *     }
 *   }
 * }
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[DependencyInjection\Attribute\AutoconfigureTag('data.processor', ['identifier' => 'resolve-markers'])]
final class ResolveMarkersProcessor implements Frontend\ContentObject\DataProcessorInterface
{
    /**
     * @param array<string, mixed> $contentObjectConfiguration
     * @param array<string, mixed> $processorConfiguration
     * @param array<array-key, mixed> $processedData
     * @return array<array-key, mixed>
     */
    public function process(
        Frontend\ContentObject\ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData,
    ): array {
        $pattern = $processorConfiguration['pattern'] ?? null;
        $removeNonMatchingMarkers = (bool)($processorConfiguration['removeNonMatchingMarkers'] ?? false);

        if ((is_string($pattern) && $pattern !== '') || $pattern === null) {
            $processor = Renderer\Variables\MarkerBasedValueProcessor::create($pattern);
            $processor->replaceMarkers($processedData, $removeNonMatchingMarkers);
        }

        return $processedData;
    }
}
