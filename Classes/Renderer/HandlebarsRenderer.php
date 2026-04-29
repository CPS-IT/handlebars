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

namespace CPSIT\Typo3Handlebars\Renderer;

use CPSIT\Typo3Handlebars\Cache;
use CPSIT\Typo3Handlebars\Event;
use CPSIT\Typo3Handlebars\Exception;
use DevTheorem\Handlebars;
use Psr\EventDispatcher;
use Psr\Http\Message;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Core;
use TYPO3\CMS\Frontend;

/**
 * HandlebarsRenderer
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[DependencyInjection\Attribute\AsAlias(Renderer::class)]
#[DependencyInjection\Attribute\Autoconfigure(tags: ['handlebars.renderer'])]
class HandlebarsRenderer implements Renderer
{
    protected ?bool $debugMode = null;

    public function __construct(
        protected readonly Cache\Cache $cache,
        protected readonly EventDispatcher\EventDispatcherInterface $eventDispatcher,
        protected readonly Helper\HelperRegistry $helperRegistry,
        protected readonly Template\TemplateResolver $templateResolver,
        protected readonly Variables\VariableBag $variableBag,
    ) {}

    public function renderTemplate(RenderingContext $context): string
    {
        $template = $context->getTemplate($this->templateResolver);

        return $this->render($template, $context);
    }

    public function renderPartial(RenderingContext $context): string
    {
        $partial = $context->getPartial($this->templateResolver);

        return $this->render($partial, $context);
    }

    protected function render(string $template, RenderingContext $context): string
    {
        $compileResult = $this->compile($template);

        // Early return if template is empty
        if ($compileResult === null) {
            return '';
        }

        // Merge variables with default variables
        $mergedVariables = array_merge($this->variableBag->get(), $context->getVariables());

        // Dispatch before rendering event
        $beforeRenderingEvent = new Event\BeforeRenderingEvent($context, $mergedVariables, $this);
        $this->eventDispatcher->dispatch($beforeRenderingEvent);

        // Render content
        $renderer = Handlebars\Handlebars::template($compileResult);
        $content = $renderer($beforeRenderingEvent->getVariables(), [
            'data' => [
                'renderingContext' => $context,
            ],
            'helpers' => $this->helperRegistry->getAll(),
            'partialResolver' => $this->resolvePartial(...),
        ]);

        // Dispatch after rendering event
        $afterRenderingEvent = new Event\AfterRenderingEvent($context, $content, $this);
        $this->eventDispatcher->dispatch($afterRenderingEvent);

        return $afterRenderingEvent->getContent();
    }

    /**
     * Compile given template by Handlebars compiler.
     */
    protected function compile(string $template): ?string
    {
        // Early return if template is empty
        if (trim($template) === '') {
            return null;
        }

        // Disable cache if debugging is enabled or caching is disabled
        if ($this->isDebugModeEnabled() || $this->isCachingDisabled()) {
            $cache = new Cache\NullCache();
        } else {
            $cache = $this->cache;
        }

        // Get compile result from cache
        $compileResult = $cache->get($template);
        if ($compileResult !== null) {
            return $compileResult;
        }

        $compileResult = Handlebars\Handlebars::precompile($template, $this->getCompileOptions());

        // Write compiled template into cache
        $cache->set($template, $compileResult);

        return $compileResult;
    }

    protected function getCompileOptions(): Handlebars\Options
    {
        return new Handlebars\Options(
            knownHelpers: $this->getKnownHelpers(),
            strict: $this->isDebugModeEnabled(),
        );
    }

    /**
     * Get currently supported helpers as stubs.
     *
     * Returns an array of available (= known) helpers to provide a list of available
     * helpers for the compiler. This is recommended to improve the usage of those
     * helpers during compile time, whereas the concrete helper callables are
     * provided during runtime.
     *
     * @return array<string, true>
     */
    protected function getKnownHelpers(): array
    {
        return array_fill_keys(array_keys($this->helperRegistry->getAll()), true);
    }

    /**
     * Resolve given partial using partial resolver.
     *
     * Tries to resolve the given partial using the {@see $templateResolver}. If
     * no partial resolver is registered, `null` is returned. Otherwise, the
     * compiled partial is returned. Returning `null` will be handled as "partial
     * not found" by the renderer.
     *
     * @param string $name Name of the partial to be resolved
     * @return \Closure|null Compiled partial if partial could be resolved, `null` otherwise
     * @throws Exception\PartialPathIsNotResolvable
     * @throws Exception\TemplateFileIsInvalid
     * @throws Exception\TemplateFormatIsNotSupported
     * @throws Exception\ViewIsNotProperlyInitialized
     */
    protected function resolvePartial(string $name): ?\Closure
    {
        $context = new RenderingContext($name);
        $template = $context->getPartial($this->templateResolver);
        $compileResult = $this->compile($template);

        if ($compileResult === null) {
            return null;
        }

        return Handlebars\Handlebars::template($compileResult);
    }

    protected function isCachingDisabled(): bool
    {
        $cacheInstruction = $this->getServerRequest()->getAttribute('frontend.cache.instruction');

        if ($cacheInstruction instanceof Frontend\Cache\CacheInstruction) {
            return !$cacheInstruction->isCachingAllowed();
        }

        return false;
    }

    protected function isDebugModeEnabled(): bool
    {
        if ($this->debugMode !== null) {
            return $this->debugMode;
        }

        $typoScript = $this->getServerRequest()->getAttribute('frontend.typoscript');

        if ($typoScript instanceof Core\TypoScript\FrontendTypoScript && (bool)($typoScript->getConfigArray()['debug'] ?? false)) {
            return true;
        }

        return $this->debugMode = (bool)($GLOBALS['TYPO3_CONF_VARS']['FE']['debug'] ?? false);
    }

    protected function getServerRequest(): Message\ServerRequestInterface
    {
        /** @var Message\ServerRequestInterface $serverRequest */
        $serverRequest = $GLOBALS['TYPO3_REQUEST'];

        return $serverRequest;
    }
}
