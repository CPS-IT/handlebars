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

namespace CPSIT\Typo3Handlebars\Frontend\Assets;

use CPSIT\Typo3Handlebars\Exception;
use TYPO3\CMS\Core;

/**
 * AssetHandler
 *
 * Processes asset configuration and registers assets with TYPO3's AssetCollector.
 * Supports JavaScript, CSS, and inline variants with full attribute and option support.
 *
 * @author Vladimir Falcon Piva <v.falcon@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final readonly class AssetHandler
{
    private const KNOWN_OPTIONS = ['priority', 'useNonce'];

    public function __construct(
        private Core\Page\AssetCollector $assetCollector,
    ) {}

    /**
     * Process and register assets from configuration.
     *
     * @param array<int|string, mixed> $assetsConfiguration Plain asset configuration array
     * @throws Exception\InvalidAssetConfigurationException
     */
    public function collectAssets(array $assetsConfiguration): void
    {
        foreach ($assetsConfiguration as $typeKey => $assets) {
            if (!is_iterable($assets)) {
                continue;
            }

            // Validate and get AssetType (validates everything)
            $assetType = $this->validateAssetAndResolveAssetType($typeKey, $assets);

            // Process validated assets
            foreach ($assets as $identifier => $configuration) {
                $this->processAsset((string)$identifier, $assetType, $configuration);
            }
        }
    }

    /**
     * @param array{source: string, attributes?: array<string, string>, options?: array<string, string>} $configuration
     */
    private function processAsset(string $identifier, AssetType $type, array $configuration): void
    {
        $source = trim((string)$configuration['source']);
        $attributes = $this->processAttributes($configuration['attributes'] ?? [], $type);
        $options = $this->processOptions($configuration['options'] ?? []);

        match ($type) {
            AssetType::JavaScript => $this->assetCollector->addJavaScript($identifier, $source, $attributes, $options),
            AssetType::InlineJavaScript => $this->assetCollector->addInlineJavaScript($identifier, $source, $attributes, $options),
            AssetType::Css => $this->assetCollector->addStyleSheet($identifier, $source, $attributes, $options),
            AssetType::InlineCss => $this->assetCollector->addInlineStyleSheet($identifier, $source, $attributes, $options),
        };
    }

    /**
     * Validate complete asset type configuration and resolve configured asset type.
     *
     * Validates:
     * - Asset type key is valid
     * - Assets is an array
     * - Each identifier is valid (string, not empty)
     * - Each asset config is valid (array with source)
     *
     * @param string|int $typeKey The asset type key from config
     * @param mixed $assets The assets configuration for this type
     * @return AssetType The validated AssetType enum
     * @throws Exception\InvalidAssetConfigurationException
     * @phpstan-assert array<string, array{source: string, attributes?: array<string, string>, options?: array<string, string>}> $assets
     */
    private function validateAssetAndResolveAssetType(string|int $typeKey, mixed $assets): AssetType
    {
        $assetType = AssetType::tryFrom((string)$typeKey);

        if ($assetType === null) {
            throw Exception\InvalidAssetConfigurationException::forUnknownAssetType((string)$typeKey);
        }

        if (!is_array($assets)) {
            throw Exception\InvalidAssetConfigurationException::forInvalidAssetsArray($assetType);
        }

        foreach ($assets as $identifier => $assetConfig) {
            if (!is_string($identifier) || trim($identifier) === '') {
                throw Exception\InvalidAssetConfigurationException::forInvalidIdentifier($assetType);
            }

            if (!is_array($assetConfig)) {
                throw Exception\InvalidAssetConfigurationException::forInvalidConfiguration($identifier, $assetType);
            }

            if (!is_string($assetConfig['source'] ?? null) || trim($assetConfig['source']) === '') {
                throw Exception\InvalidAssetConfigurationException::forMissingSource($identifier, $assetType);
            }
        }

        return $assetType;
    }

    /**
     * Process and normalize HTML attributes.
     *
     * @return array<string, string>
     */
    private function processAttributes(mixed $attributes, AssetType $type): array
    {
        if (!is_array($attributes)) {
            return [];
        }

        $processed = [];

        foreach ($attributes as $name => $value) {
            $name = (string)$name;
            $this->addAttribute($processed, $name, $value, $type);
        }

        return $processed;
    }

    /**
     * @param array<string, string> $processed
     */
    private function addAttribute(array &$processed, string $name, mixed $value, AssetType $type): void
    {
        if ($type->isBooleanAttribute($name)) {
            $this->addBooleanAttribute($processed, $name, $value);
        } else {
            $this->addRegularAttribute($processed, $name, $value);
        }
    }

    /**
     * Add a boolean attribute (e.g., async, defer, disabled).
     *
     * @param array<string, string> $processed
     */
    private function addBooleanAttribute(array &$processed, string $name, mixed $value): void
    {
        if ((bool)$value === true) {
            $processed[$name] = $name;
        }
    }

    /**
     * @param array<string, string> $processed
     */
    private function addRegularAttribute(array &$processed, string $name, mixed $value): void
    {
        if (is_scalar($value) && (string)$value !== '') {
            $processed[$name] = (string)$value;
        }
    }

    /**
     * Process AssetCollector options.
     *
     * @param array<string, mixed> $options
     * @return array<string, bool>
     */
    private function processOptions(array $options): array
    {
        $processed = [];

        foreach (self::KNOWN_OPTIONS as $option) {
            if (isset($options[$option])) {
                $processed[$option] = (bool)$options[$option];
            }
        }

        return $processed;
    }
}
