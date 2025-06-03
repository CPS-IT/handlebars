<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2020 Elias Häußler <e.haeussler@familie-redlich.de>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace Fr\Typo3Handlebars\Renderer;

use DevTheorem\Handlebars;
use Fr\Typo3Handlebars\Cache;
use Fr\Typo3Handlebars\Event;
use Fr\Typo3Handlebars\Exception;
use Psr\EventDispatcher;
use Psr\Log;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Frontend;

/**
 * HandlebarsRenderer
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[DependencyInjection\Attribute\AsAlias('handlebars.renderer')]
#[DependencyInjection\Attribute\Autoconfigure(tags: ['handlebars.renderer'])]
class HandlebarsRenderer implements Renderer
{
    protected readonly bool $debugMode;

    public function __construct(
        #[DependencyInjection\Attribute\Autowire('@handlebars.cache')]
        protected readonly Cache\Cache $cache,
        protected readonly EventDispatcher\EventDispatcherInterface $eventDispatcher,
        protected readonly Helper\HelperRegistry $helperRegistry,
        protected readonly Log\LoggerInterface $logger,
        #[DependencyInjection\Attribute\Autowire('@handlebars.template_resolver')]
        protected readonly Template\TemplateResolver $templateResolver,
        protected readonly Variables\VariableBag $variableBag,
    ) {
        $this->debugMode = $this->isDebugModeEnabled();
    }

    public function render(Template\View\HandlebarsView $view): string
    {
        try {
            return $this->processRendering($view);
        } catch (Exception\TemplateCompilationException | Exception\TemplateFileIsInvalid | Exception\TemplateFormatIsNotSupported | Exception\TemplatePathIsNotResolvable | Exception\ViewIsNotProperlyInitialized $exception) {
            $this->logger->critical($exception->getMessage(), ['exception' => $exception]);

            return '';
        }
    }

    /**
     * @throws Exception\TemplateFileIsInvalid
     * @throws Exception\TemplateFormatIsNotSupported
     * @throws Exception\TemplatePathIsNotResolvable
     * @throws Exception\ViewIsNotProperlyInitialized
     */
    protected function processRendering(Template\View\HandlebarsView $view): string
    {
        $compileResult = $this->compile($view);

        // Early return if template is empty
        if ($compileResult === null) {
            return '';
        }

        // Merge variables with default variables
        $mergedVariables = array_merge($this->variableBag->get(), $view->getVariables());

        // Dispatch before rendering event
        $beforeRenderingEvent = new Event\BeforeRenderingEvent($view, $mergedVariables, $this);
        $this->eventDispatcher->dispatch($beforeRenderingEvent);

        // Render content
        $renderer = Handlebars\Handlebars::template($compileResult);
        $content = $renderer($beforeRenderingEvent->getVariables(), [
            'helpers' => $this->helperRegistry->getAll(),
        ]);

        // Dispatch after rendering event
        $afterRenderingEvent = new Event\AfterRenderingEvent($view, $content, $this);
        $this->eventDispatcher->dispatch($afterRenderingEvent);

        return $afterRenderingEvent->getContent();
    }

    /**
     * Compile given template by Handlebars compiler.
     *
     * @throws Exception\TemplateFileIsInvalid
     * @throws Exception\TemplateFormatIsNotSupported
     * @throws Exception\TemplatePathIsNotResolvable
     * @throws Exception\ViewIsNotProperlyInitialized
     */
    protected function compile(Template\View\HandlebarsView $view): ?string
    {
        $template = $view->getTemplate($this->templateResolver);

        // Early return if template is empty
        if (\trim($template) === '') {
            return null;
        }

        // Disable cache if debugging is enabled or caching is disabled
        if ($this->debugMode || $this->isCachingDisabled()) {
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
        if (!$this->debugMode) {
            $cache->set($template, $compileResult);
        }

        return $compileResult;
    }

    protected function getCompileOptions(): Handlebars\Options
    {
        return new Handlebars\Options(
            strict: $this->debugMode,
            helpers: $this->getHelperStubs(),
            partialResolver: fn(Handlebars\Context $context, string $name) => $this->resolvePartial($name),
        );
    }

    /**
     * Get currently supported helpers as stubs.
     *
     * Returns an array of available helper stubs to provide a list of available
     * helpers for the compiler. This is necessary to enforce the usage of those
     * helpers during compile time, whereas the concrete helper callables are
     * provided during runtime.
     *
     * @return array<string, callable>
     */
    protected function getHelperStubs(): array
    {
        return array_fill_keys(array_keys($this->helperRegistry->getAll()), static fn() => '');
    }

    /**
     * Resolve path to given partial using partial resolver.
     *
     * Tries to resolve the given partial using the {@see $templateResolver}. If
     * no partial resolver is registered, `null` is returned. Otherwise, the
     * partials' file contents are returned. Returning `null` will be handled as
     * "partial not found" by the renderer.
     *
     * This method is called by {@see Handlebars\Partial::resolve()}.
     *
     * @param string $name Name of the partial to be resolved
     * @return string|null Partial file contents if partial could be resolved, `null` otherwise
     * @throws Exception\PartialPathIsNotResolvable
     * @throws Exception\TemplateFormatIsNotSupported
     */
    protected function resolvePartial(string $name): ?string
    {
        $partial = @file_get_contents($this->templateResolver->resolvePartialPath($name));

        if ($partial === false) {
            return null;
        }

        return $partial;
    }

    protected function isCachingDisabled(): bool
    {
        $tsfe = $this->getTypoScriptFrontendController();
        return $tsfe !== null && (bool)$tsfe->no_cache;
    }

    protected function isDebugModeEnabled(): bool
    {
        $tsfe = $this->getTypoScriptFrontendController();
        if ($tsfe !== null && (bool)($tsfe->config['config']['debug'] ?? false)) {
            return true;
        }
        return (bool)($GLOBALS['TYPO3_CONF_VARS']['FE']['debug'] ?? false);
    }

    protected function getTypoScriptFrontendController(): ?Frontend\Controller\TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'] ?? null;
    }
}
