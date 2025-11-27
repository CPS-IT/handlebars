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

use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Core;
use TYPO3\CMS\Frontend;

/**
 * Data processor to unflatten nested variable names, converting them from `foo.baz.bar` to `foo { baz { bar = ... }}`.
 *
 * Example
 * =======
 *
 * page = HANDLEBARSTEMPLATE
 * page {
 *   templateName = @page
 *
 *   # ...
 *
 *   dataProcessing {
 *     10 = menu
 *     10 {
 *       # ...
 *
 *       as = page.pageHeader.nav.mainMenu
 *     }
 *
 *     20 = unflatten-variable-names
 *   }
 * }
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[DependencyInjection\Attribute\AutoconfigureTag('data.processor', ['identifier' => 'unflatten-variable-names'])]
final readonly class UnflattenVariableNamesProcessor implements Frontend\ContentObject\DataProcessorInterface
{
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
        return Core\Utility\ArrayUtility::unflatten($processedData);
    }
}
