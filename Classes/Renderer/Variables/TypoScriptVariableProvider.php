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

use CPSIT\Typo3Handlebars\Extension;
use Psr\Http\Message;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Frontend;

/**
 * TypoScriptVariableProvider
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class TypoScriptVariableProvider implements RequestAwareVariableProvider
{
    /**
     * @var array<string|int, mixed>|null
     */
    private ?array $variables = null;
    private ?Message\ServerRequestInterface $request = null;

    public function __construct(
        private readonly Extbase\Configuration\ConfigurationManagerInterface $configurationManager,
        private readonly Frontend\ContentObject\ContentDataProcessor $contentDataProcessor,
        private readonly Core\TypoScript\TypoScriptService $typoScriptService,
    ) {}

    public function get(): array
    {
        $this->variables ??= $this->fetchVariables();

        return $this->variables ?? [];
    }

    public function isCacheable(): bool
    {
        // Once we have a request attached, the resulting variables can be safely cached.
        // Otherwise, caching should be avoided until we can resolve and process all variables
        // in the context of the given request.
        return $this->request !== null;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->get()[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get()[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): never
    {
        throw new \LogicException('Variables cannot be modified.', 1736274326);
    }

    public function offsetUnset(mixed $offset): never
    {
        throw new \LogicException('Variables cannot be modified.', 1736274336);
    }

    public function setRequest(Message\ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    public static function getPriority(): int
    {
        return 50;
    }

    /**
     * @return array<string|int, mixed>|null
     */
    private function fetchVariables(): ?array
    {
        $cObj = $this->resolveContentObjectRenderer();

        // Early return if content object renderer is not (yet) available, but don't persist
        // anything to allow variable resolution at a later time, where cObj might be available.
        if ($cObj === null) {
            return null;
        }

        $typoScriptConfiguration = $this->typoScriptService->convertPlainArrayToTypoScriptArray(
            $this->configurationManager->getConfiguration(
                Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
                Extension::NAME,
            ),
        );

        // Early return if variables are invalid
        if (!is_array($variables = $typoScriptConfiguration['variables.'] ?? null)) {
            return [];
        }

        // Process content object variables and simple variables
        $variables = VariablesProcessor::for($cObj)->process($variables);

        // Process variables with configured data processors
        return $this->contentDataProcessor->process($cObj, $typoScriptConfiguration, $variables);
    }

    private function resolveContentObjectRenderer(): ?Frontend\ContentObject\ContentObjectRenderer
    {
        $contentObjectRenderer = $this->request?->getAttribute('currentContentObject');

        if (!($contentObjectRenderer instanceof Frontend\ContentObject\ContentObjectRenderer)) {
            return null;
        }

        return $contentObjectRenderer;
    }
}
