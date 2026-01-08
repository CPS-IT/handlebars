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

namespace CPSIT\Typo3Handlebars\Service;

use CPSIT\Typo3Handlebars\Exception;
use CPSIT\Typo3Handlebars\Exception\InvalidAssetConfigurationException;
use TYPO3\CMS\Core\Page\AssetCollector;

/**
 * AssetService
 *
 * Processes asset configuration and registers assets with TYPO3's AssetCollector.
 * Supports JavaScript, CSS, and inline variants with full attribute and option support.
 *
 * @author Vladimir Falcon Piva <v.falcon@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class AssetService
{
    private const KNOWN_OPTIONS = ['priority', 'useNonce'];

    public function __construct(
        private readonly AssetCollector $assetCollector,
    ) {}

    /**
     * Process and register assets from configuration.
     *
     * @param array<int|string, mixed> $assetsConfig Plain asset configuration array
     * @throws Exception\InvalidAssetConfigurationException
     */
    public function registerAssets(array $assetsConfig): void
    {
        foreach ($assetsConfig as $typeKey => $assets) {
            // Validate and get AssetType (validates everything)
            $assetType = $this->validateConfiguration($typeKey, $assets);

            // Process validated assets
            $this->processAssets($assetType, $assets);
        }
    }

    /**
     * @param array<int|string, mixed> $assets
     */
    private function processAssets(
        AssetType $type,
        array $assets,
    ): void {
        foreach ($assets as $identifier => $assetConfig) {
            $this->processAsset((string)$identifier, $type, $assetConfig);
        }
    }

    /**
     * @param array<string, mixed> $assetConfig
     */
    private function processAsset(
        string $identifier,
        AssetType $type,
        array $assetConfig,
    ): void {
        $source = \trim((string)$assetConfig['source']);

        $attributes = $this->processAttributes(
            $assetConfig['attributes'] ?? [],
            $type,
        );
        $options = $this->processOptions($assetConfig['options'] ?? []);

        $this->registerWithCollector($type, $identifier, $source, $attributes, $options);
    }

    /**
     * Validate complete asset type configuration.
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
     * @throws InvalidAssetConfigurationException
     */
    private function validateConfiguration(
        string|int $typeKey,
        mixed $assets,
    ): AssetType {
        $assetType = AssetType::tryFrom((string)$typeKey);

        if ($assetType === null) {
            throw Exception\InvalidAssetConfigurationException::forUnknownAssetType((string)$typeKey);
        }

        if (!\is_array($assets)) {
            throw Exception\InvalidAssetConfigurationException::forInvalidAssetsArray($assetType->value);
        }

        foreach ($assets as $identifier => $assetConfig) {
            if (!\is_string($identifier) || \trim($identifier) === '') {
                throw Exception\InvalidAssetConfigurationException::forInvalidIdentifier($assetType->value);
            }

            if (!\is_array($assetConfig)) {
                throw Exception\InvalidAssetConfigurationException::forInvalidConfiguration($identifier, $assetType->value);
            }

            if (!isset($assetConfig['source']) || \trim((string)$assetConfig['source']) === '') {
                throw Exception\InvalidAssetConfigurationException::forMissingSource($identifier, $assetType->value);
            }
        }

        return $assetType;
    }

    /**
     * Register asset with AssetCollector using the appropriate method.
     *
     * @param array<string, string> $attributes
     * @param array<string, bool> $options
     */
    private function registerWithCollector(
        AssetType $type,
        string $identifier,
        string $source,
        array $attributes,
        array $options,
    ): void {
        $method = $type->getCollectorMethod();
        $this->assetCollector->$method($identifier, $source, $attributes, $options);
    }

    /**
     * Process and normalize HTML attributes.
     *
     * @return array<string, string>
     */
    private function processAttributes(mixed $attributes, AssetType $type): array
    {
        if (!\is_array($attributes)) {
            return [];
        }

        $processed = [];

        foreach ($attributes as $name => $value) {
            $name = (string)$name;
            $processed = $this->addAttribute($processed, $name, $value, $type);
        }

        return $processed;
    }

    /**
     * @param array<string, string> $processed
     * @return array<string, string>
     */
    private function addAttribute(
        array $processed,
        string $name,
        mixed $value,
        AssetType $type,
    ): array {
        if ($type->isBooleanAttribute($name)) {
            return $this->addBooleanAttribute($processed, $name, $value);
        }

        return $this->addRegularAttribute($processed, $name, $value);
    }

    /**
     * Add a boolean attribute (e.g., async, defer, disabled).
     *
     * @param array<string, string> $processed
     * @return array<string, string>
     */
    private function addBooleanAttribute(array $processed, string $name, mixed $value): array
    {
        if ((bool)$value === true) {
            $processed[$name] = $name;
        }
        return $processed;
    }

    /**
     * @param array<string, string> $processed
     * @return array<string, string>
     */
    private function addRegularAttribute(array $processed, string $name, mixed $value): array
    {
        if ($value !== null && $value !== '') {
            $processed[$name] = (string)$value;
        }
        return $processed;
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
