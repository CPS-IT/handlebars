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

use CPSIT\Typo3Handlebars\Exception;
use CPSIT\Typo3Handlebars\Renderer;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Frontend;

/**
 * Data processor to process given variables, especially useful in combination with other data processors.
 *
 * Example (standalone):
 * =====================
 *
 * hero = HANDLEBARSTEMPLATE
 * hero {
 *   templateName = @hero
 *
 *   # ...
 *
 *   dataProcessing {
 *     10 = process-variables
 *     10 {
 *       if.isTrue.field = title
 *
 *       variables {
 *         header = TEXT
 *         header.field = title
 *
 *         teaser = TEXT
 *         teaser.field = teaser
 *         teaser.parseFunc < lib.parseFunc_RTE
 *
 *         # ...
 *       }
 *     }
 *   }
 * }
 *
 * Example (with other data processor):
 * ====================================
 *
 * accordion = HANDLEBARSTEMPLATE
 * accordion {
 *   templateName = @accordion
 *
 *   # ...
 *
 *   dataProcessing {
 *     10 = database-query
 *     10 {
 *       # ...
 *
 *       dataProcessing {
 *         10 = process-variables
 *         10 {
 *           table = tx_mysitepackage_domain_model_accordion_element
 *           as = accordionItem
 *           variables {
 *             template = @accordion-item
 *
 *             header = TEXT
 *             header.field = title
 *
 *             bodytext = TEXT
 *             bodytext.field = bodytext
 *             bodytext.parseFunc < lib.parseFunc_RTE
 *
 *             # ...
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
#[DependencyInjection\Attribute\AutoconfigureTag('data.processor', ['identifier' => 'process-variables'])]
final readonly class ProcessVariablesProcessor implements Frontend\ContentObject\DataProcessorInterface
{
    /**
     * @param array<string, mixed> $contentObjectConfiguration
     * @param array<string, mixed> $processorConfiguration
     * @param array<string|int, mixed> $processedData
     * @return array<string|int, mixed>
     * @throws Frontend\ContentObject\Exception\ContentRenderingException
     * @throws Exception\ReservedVariableCannotBeUsed
     */
    public function process(
        Frontend\ContentObject\ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData,
    ): array {
        $data = $processorConfiguration['data.'] ?? $processedData['data'] ?? $cObj->data;
        $table = $processorConfiguration['table'] ?? $processedData['table'] ?? $cObj->getCurrentTable();
        $variables = $processorConfiguration['variables.'] ?? null;
        $as = $processorConfiguration['as'] ?? null;

        // Early return if no variables to process are configured
        if (!\is_array($variables)) {
            return $processedData;
        }

        // Use temporary cObj for processing
        $cObj = clone $cObj;
        $cObj->start($data, $table);

        // Early return if processing should be skipped according to a configured condition
        if (\is_array($processorConfiguration['if.'] ?? null) && !$cObj->checkIf($processorConfiguration['if.'])) {
            return $processedData;
        }

        // Process variables with temporary cObj
        $processor = Renderer\Variables\VariablesProcessor::for($cObj);
        $processedVariables = $processor->process($variables);

        // Apply processed variables, either override processed data (if no target variable name is given)
        // or merge with processed data using given target variable name ("as")
        if ($as === null) {
            $processedData = $processedVariables;
        } else {
            $processedData[$as] = $processedVariables;
        }

        return $processedData;
    }
}
