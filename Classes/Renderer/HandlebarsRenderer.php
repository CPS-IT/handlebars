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
    public function __construct(
        protected readonly Cache\Cache $cache,
        protected readonly EventDispatcher\EventDispatcherInterface $eventDispatcher,
        protected readonly Helper\HelperRegistry $helperRegistry,
        protected readonly Template\TemplateResolver $templateResolver,
        protected readonly Variables\VariableBag $variableBag,
    ) {}

    public function renderTemplate(RenderingContext $context): string
    {
        return $this->render($context, $context->getTemplate(...));
    }

    public function renderPartial(RenderingContext $context): string
    {
        return $this->render($context, $context->getPartial(...));
    }

    /**
     * @param \Closure(Template\TemplateResolver): string $templateResolver
     */
    protected function render(RenderingContext $context, \Closure $templateResolver): string
    {
        $this->eventDispatcher->dispatch(new Event\BeforeTemplateCompilationEvent($context, $this));

        // Resolve and compile template
        $template = $templateResolver($this->templateResolver);
        $compileResult = $this->compile($template, $context->getRequest());

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
    protected function compile(string $template, ?Message\ServerRequestInterface $request = null): string
    {
        if ($this->isCachingDisabled($request)) {
            $cache = new Cache\NullCache();
        } else {
            $cache = $this->cache;
        }

        $compileOptions = $this->getCompileOptions();
        $cacheContext = new Cache\CacheContext($template, $compileOptions);

        // Get compile result from cache
        $compileResult = $cache->get($cacheContext);
        if ($compileResult !== null) {
            return $compileResult;
        }

        $compileResult = Handlebars\Handlebars::precompile($template, $compileOptions);

        // Write compiled template into cache
        $cache->set($cacheContext, $compileResult);

        return $compileResult;
    }

    protected function getCompileOptions(): Handlebars\Options
    {
        return new Handlebars\Options(
            knownHelpers: $this->getKnownHelpers(),
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
     * Tries to resolve the given partial using the {@see $templateResolver}
     * and returns the compiled partial.
     *
     * @param string $name Name of the partial to be resolved
     * @return \Closure Compiled partial if partial could be resolved
     * @throws Exception\PartialPathIsNotResolvable
     * @throws Exception\TemplateFileIsInvalid
     * @throws Exception\TemplateFormatIsNotSupported
     * @throws Exception\ViewIsNotProperlyInitialized
     */
    protected function resolvePartial(string $name): \Closure
    {
        $context = new RenderingContext($name);
        $template = $context->getPartial($this->templateResolver);
        $compileResult = $this->compile($template);

        return Handlebars\Handlebars::template($compileResult);
    }

    protected function isCachingDisabled(?Message\ServerRequestInterface $request = null): bool
    {
        $request ??= $this->getServerRequest();
        $cacheInstruction = $request->getAttribute('frontend.cache.instruction');

        if ($cacheInstruction instanceof Frontend\Cache\CacheInstruction) {
            return !$cacheInstruction->isCachingAllowed();
        }

        return false;
    }

    protected function getServerRequest(): Message\ServerRequestInterface
    {
        /** @var Message\ServerRequestInterface $serverRequest */
        $serverRequest = $GLOBALS['TYPO3_REQUEST'];

        return $serverRequest;
    }
}
