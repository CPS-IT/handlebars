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

namespace CPSIT\Typo3Handlebars\Renderer\Variables;

use CPSIT\Typo3Handlebars\Exception;
use TYPO3\CMS\Core;
use TYPO3\CMS\Frontend;

/**
 * VariablesProcessor
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 */
final readonly class VariablesProcessor
{
    private function __construct(
        private Frontend\ContentObject\ContentObjectRenderer $contentObjectRenderer,
    ) {}

    public static function for(Frontend\ContentObject\ContentObjectRenderer $contentObjectRenderer): self
    {
        return new self($contentObjectRenderer);
    }

    /**
     * @param array<string|int, mixed> $variables
     * @return array<string|int, mixed>
     * @throws Exception\ReservedVariableCannotBeUsed
     * @throws Frontend\ContentObject\Exception\ContentRenderingException
     */
    public function process(array $variables): array
    {
        $variablesToProcess = [];
        $simpleVariables = [];

        foreach ($variables as $name => $value) {
            if (isset($variablesToProcess[$name])) {
                continue;
            }

            // Use sanitized variable name for simple variables
            $sanitizedName = \rtrim((string)$name, '.');

            // Apply variable as simple variable if it's a complex structure (such as objects)
            if (!is_string($value) && !\is_array($value)) {
                $simpleVariables[$sanitizedName] = $value;

                continue;
            }

            // Register variable for further processing if an appropriate content object is available
            // or if variable is a reference to another variable (will be resolved later). The whitespace
            // after left angle bracket is intended to avoid treating static text like <foo> as reference.
            // Since all refernces are written like =< foo, we can safely assume a combination of a left
            // angle bracket followed by a whitespace is a reference to be resolved.
            if (is_string($value) &&
                ($this->contentObjectRenderer->getContentObject($value) !== null || str_starts_with($value, '< '))
            ) {
                $cObjConfName = $name . '.';
                $variablesToProcess[$name] = $value;

                if (isset($variables[$cObjConfName])) {
                    $variablesToProcess[$cObjConfName] = $variables[$cObjConfName];
                }

                continue;
            }

            // Apply variable as simple variable if it's a simple construct
            // (including arrays, which will be processed recursively as they may contain content objects)
            if (!\is_array($value)) {
                $simpleVariables[$sanitizedName] = $value;
            } elseif (!$this->shouldRemoveVariable($value)) {
                unset($value['removeIf.']);
                $simpleVariables[$sanitizedName] = $this->process($value);
            }
        }

        // Return only simple variables if no variables need to be processed
        if ($variablesToProcess === []) {
            return $simpleVariables;
        }

        // Process content object variables
        $processedVariables = $this->getContentObjectVariables($variablesToProcess);

        // Merged processed content object variables with simple variables
        Core\Utility\ArrayUtility::mergeRecursiveWithOverrule($processedVariables, $simpleVariables);

        return $processedVariables;
    }

    /**
     * @param array<string|int, mixed> $variables
     * @return array<string|int, mixed>
     * @throws Exception\ReservedVariableCannotBeUsed
     * @see https://github.com/TYPO3/typo3/blob/v13.4.13/typo3/sysext/frontend/Classes/ContentObject/FluidTemplateContentObject.php#L228
     */
    private function getContentObjectVariables(array $variables): array
    {
        $processedVariables = [];
        $reservedVariables = ['data', 'current'];

        foreach ($variables as $variableName => $cObjType) {
            if (is_array($cObjType)) {
                continue;
            }

            if (in_array($variableName, $reservedVariables, true)) {
                throw new Exception\ReservedVariableCannotBeUsed($variableName);
            }

            $cObjConf = $variables[$variableName . '.'] ?? [];

            // Process value
            $value = $this->contentObjectRenderer->cObjGetSingle($cObjType, $cObjConf, 'variables.' . $variableName);

            // Check if value should *not* be applied after processing
            $removeVariable = $this->shouldRemoveVariable($cObjConf, $value);

            // Apply value if not empty or no *empty toggle* is set
            if (!$removeVariable && trim($value) !== '') {
                $processedVariables[$variableName] = $value;
            }
        }

        return $processedVariables;
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private function shouldRemoveVariable(array $configuration, ?string $value = null): bool
    {
        $removeCondition = $configuration['removeIf.'] ?? null;

        // Early return on missing or insufficient remove condition
        if (!\is_array($removeCondition)) {
            return false;
        }

        // Use processed value as current value
        $currentValue = $this->contentObjectRenderer->getCurrentVal();
        $this->contentObjectRenderer->setCurrentVal($value);

        try {
            return $this->contentObjectRenderer->checkIf($removeCondition);
        } finally {
            // Restore original current value
            $this->contentObjectRenderer->setCurrentVal($currentValue);
        }
    }
}
