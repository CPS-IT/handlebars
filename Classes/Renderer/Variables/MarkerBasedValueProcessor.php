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

/**
 * MarkerBasedValueProcessor
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 */
final class MarkerBasedValueProcessor
{
    private const DEFAULT_PATTERN = '/###(\w+)###/';

    /**
     * @var array<string, array<string, string|null>>
     */
    private static array $markerTemplateCache = [];

    /**
     * @var array<string, array{array<string, mixed>, string|int, mixed}>
     */
    private array $markerValues = [];

    /**
     * @var array<string, list<array{array<string, mixed>, string|int, mixed}>>
     */
    private array $replacementTargets = [];

    /**
     * @param non-empty-string $markerPattern
     */
    private function __construct(
        private readonly string $markerPattern,
    ) {
        self::$markerTemplateCache[$this->markerPattern] ??= [];
    }

    /**
     * @param non-empty-string|null $markerPattern
     * @throws Exception\MarkerPatternIsInvalid
     */
    public static function create(?string $markerPattern = null): self
    {
        if ($markerPattern !== null && !self::isValidPattern($markerPattern)) {
            throw new Exception\MarkerPatternIsInvalid($markerPattern);
        }

        return new self($markerPattern ?? self::DEFAULT_PATTERN);
    }

    private static function isValidPattern(string $markerPattern): bool
    {
        return @preg_match($markerPattern, '') !== false;
    }

    /**
     * @param array<array-key, mixed> $values
     */
    public function replaceMarkers(array &$values, bool $removeNonMatchingMarkers = false): int
    {
        $this->collectReferencesRecursively($values);

        $replacedMarkerValues = 0;

        foreach ($this->replacementTargets as $marker => $targets) {
            $removeTarget = false;

            if (!isset($this->markerValues[$marker])) {
                if ($removeNonMatchingMarkers) {
                    $removeTarget = true;
                } else {
                    continue;
                }
            }

            foreach ($targets as &$target) {
                [&$targetParent, $targetKey, &$targetValue] = $target;

                if ($removeTarget) {
                    unset($targetParent[$targetKey]);
                    continue;
                }

                [&$markerParent, $markerKey, $value] = $this->markerValues[$marker];
                $targetValue = $value;
                $replacedMarkerValues++;
                unset($markerParent[$markerKey]);
            }
        }

        return $replacedMarkerValues;
    }

    /**
     * @param array<array-key, mixed> $values
     */
    private function collectReferencesRecursively(array &$values): void
    {
        foreach ($values as $key => &$value) {
            $this->collectReferences($value, $key, $values);
        }
    }

    /**
     * @param array<array-key, mixed> $parent
     */
    private function collectReferences(mixed &$value, string|int $key, array &$parent): void
    {
        if (is_array($value)) {
            $this->collectReferencesRecursively($value);
        }
        if (is_string($key) && ($marker = $this->resolveMarker($key)) !== null) {
            $this->markerValues[$marker] = [&$parent, $key, $value];
        }
        if (is_string($value) && ($marker = $this->resolveMarker($value)) !== null) {
            $this->replacementTargets[$marker] ??= [];
            $this->replacementTargets[$marker][] = [&$parent, $key, &$value];
        }
    }

    private function resolveMarker(string $value): ?string
    {
        if (array_key_exists($value, self::$markerTemplateCache[$this->markerPattern])) {
            return self::$markerTemplateCache[$this->markerPattern][$value];
        }

        preg_match($this->markerPattern, $value, $matches);

        return self::$markerTemplateCache[$this->markerPattern][$value] = $matches[1] ?? null;
    }
}
