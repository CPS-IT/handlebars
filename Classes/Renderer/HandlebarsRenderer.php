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

use Fr\Typo3Handlebars\Cache\CacheInterface;
use Fr\Typo3Handlebars\Cache\NullCache;
use Fr\Typo3Handlebars\Event\AfterRenderingEvent;
use Fr\Typo3Handlebars\Event\BeforeRenderingEvent;
use Fr\Typo3Handlebars\Exception\InvalidTemplateFileException;
use Fr\Typo3Handlebars\Exception\TemplateCompilationException;
use Fr\Typo3Handlebars\Exception\TemplateNotFoundException;
use Fr\Typo3Handlebars\Renderer\Helper\HelperRegistry;
use Fr\Typo3Handlebars\Renderer\Template\TemplateResolverInterface;
use LightnCandy\Context;
use LightnCandy\LightnCandy;
use LightnCandy\Partial;
use LightnCandy\Runtime;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * HandlebarsRenderer
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[AsAlias('handlebars.renderer')]
#[Autoconfigure(tags: ['handlebars.renderer'])]
class HandlebarsRenderer implements RendererInterface
{
    protected readonly bool $debugMode;

    /**
     * @param array<string|int, mixed> $defaultData
     */
    public function __construct(
        #[Autowire('@handlebars.cache')]
        protected readonly CacheInterface $cache,
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly HelperRegistry $helperRegistry,
        protected readonly LoggerInterface $logger,
        #[Autowire('@handlebars.template_resolver')]
        protected readonly TemplateResolverInterface $templateResolver,
        #[Autowire('@handlebars.partial_resolver')]
        protected readonly ?TemplateResolverInterface $partialResolver = null,
        #[Autowire('%handlebars.default_data%')]
        protected array $defaultData = [],
    ) {
        $this->debugMode = $this->isDebugModeEnabled();
    }

    public function render(string $templatePath, array $data = []): string
    {
        try {
            return $this->processRendering($templatePath, $data);
        } catch (InvalidTemplateFileException | TemplateCompilationException | TemplateNotFoundException $exception) {
            $this->logger->critical($exception->getMessage(), ['exception' => $exception]);

            return '';
        }
    }

    /**
     * @param array<string|int, mixed> $data
     * @throws InvalidTemplateFileException if template file is invalid
     * @throws TemplateCompilationException if template compilation fails and errors are not yet handled by compiler
     * @throws TemplateNotFoundException if template could not be found
     */
    protected function processRendering(string $templatePath, array $data): string
    {
        $fullTemplatePath = $this->templateResolver->resolveTemplatePath($templatePath);
        $template = file_get_contents($fullTemplatePath);

        // Throw exception if template file is invalid
        if ($template === false) {
            throw new InvalidTemplateFileException($fullTemplatePath, 1606217313);
        }

        // Early return if template is empty
        if (trim($template) === '') {
            return '';
        }

        // Merge render data with default data
        $mergedData = array_merge($this->defaultData, $data);

        // Compile template
        $compileResult = $this->compile($template);
        $renderer = $this->prepareCompileResult($compileResult);

        // Dispatch before rendering event
        $beforeRenderingEvent = new BeforeRenderingEvent($fullTemplatePath, $mergedData, $this);
        $this->eventDispatcher->dispatch($beforeRenderingEvent);

        // Render content
        $content = $renderer($beforeRenderingEvent->getData(), [
            'debug' => Runtime::DEBUG_TAGS_HTML,
            'helpers' => $this->helperRegistry->getAll(),
        ]);

        // Dispatch after rendering event
        $afterRenderingEvent = new AfterRenderingEvent($fullTemplatePath, $content, $this);
        $this->eventDispatcher->dispatch($afterRenderingEvent);

        return $afterRenderingEvent->getContent();
    }

    /**
     * Compile given template by LightnCandy compiler.
     *
     * @param string $template Raw template to be compiled
     * @return string The compiled template
     * @throws TemplateCompilationException if template compilation fails and errors are not yet handled by compiler
     */
    protected function compile(string $template): string
    {
        // Disable cache if debugging is enabled or caching is disabled
        $cache = $this->cache;
        if ($this->debugMode || $this->isCachingDisabled()) {
            $cache = new NullCache();
        }

        // Get compile result from cache
        $compileResult = $cache->get($template);
        if ($compileResult !== null) {
            return $compileResult;
        }

        $compileResult = LightnCandy::compile($template, $this->getCompileOptions());

        // Handle compilation failures
        if ($compileResult === false) {
            $errors = LightnCandy::getContext()['error'] ?? [];

            throw new TemplateCompilationException(
                \sprintf(
                    'Error during template compilation: "%s"',
                    implode('", "', \is_array($errors) ? $errors : [$errors])
                ),
                1614620212
            );
        }

        // Write compiled template into cache
        if (!$this->debugMode) {
            $cache->set($template, $compileResult);
        }

        return $compileResult;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getCompileOptions(): array
    {
        return [
            'flags' => $this->getCompileFlags(),
            'helpers' => $this->getHelperStubs(),
            'partialresolver' => $this->partialResolver !== null ? $this->resolvePartial(...) : false,
        ];
    }

    protected function getCompileFlags(): int
    {
        $flags = LightnCandy::FLAG_HANDLEBARS | LightnCandy::FLAG_RUNTIMEPARTIAL | LightnCandy::FLAG_EXTHELPER | LightnCandy::FLAG_ERROR_EXCEPTION;
        if ($this->debugMode) {
            $flags |= LightnCandy::FLAG_RENDER_DEBUG;
        }
        return $flags;
    }

    protected function prepareCompileResult(string $compileResult): callable
    {
        // Touch temporary file
        $path = GeneralUtility::tempnam('hbs_');

        // Write file and validate write result
        /** @var string|null $writeResult */
        $writeResult = GeneralUtility::writeFileToTypo3tempDir($path, '<?php ' . $compileResult);
        if ($writeResult !== null) {
            throw new TemplateCompilationException(\sprintf('Cannot prepare compiled render function: %s', $writeResult), 1614705397);
        }

        // Build callable
        $callable = include $path;

        // Remove temporary file
        GeneralUtility::unlink_tempfile($path);

        // Validate callable
        if (!\is_callable($callable)) {
            throw new TemplateCompilationException('Got invalid compile result from compiler.', 1639405571);
        }

        return $callable;
    }

    /**
     * Get currently supported helpers as stubs.
     *
     * Returns an array of available helper stubs to provide a list of available
     * helpers for the compiler. This is necessary to enforce the usage of those
     * helpers during compile time, whereas the concrete helper callables are
     * provided during runtime.
     *
     * @return array<string, true>
     */
    protected function getHelperStubs(): array
    {
        return array_fill_keys(array_keys($this->helperRegistry->getAll()), true);
    }

    /**
     * Resolve path to given partial using partial resolver.
     *
     * Tries to resolve the given partial using the {@see $partialResolver}. If
     * no partial resolver is registered, `null` is returned. Otherwise, the
     * partials' file contents are returned. Returning `null` will be handled as
     * "partial not found" by the renderer.
     *
     * This method is called by {@see Partial::resolver()}.
     *
     * @param array<string, mixed> $context Current context of compiler progress, see {@see Context::create()}
     * @param string $name Name of the partial to be resolved
     * @return string|null Partial file contents if partial could be resolved, `null` otherwise
     * @throws TemplateNotFoundException if partial could not be found
     */
    public function resolvePartial(/** @noinspection PhpUnusedParameterInspection */ array $context, string $name): ?string
    {
        if ($this->partialResolver === null) {
            return null;
        }
        return file_get_contents($this->partialResolver->resolveTemplatePath($name)) ?: null;
    }

    /**
     * @return array<string|int, mixed>
     */
    public function getDefaultData(): array
    {
        return $this->defaultData;
    }

    /**
     * @param array<string|int, mixed> $defaultData
     */
    public function setDefaultData(array $defaultData): self
    {
        $this->defaultData = $defaultData;
        return $this;
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

    protected function getTypoScriptFrontendController(): ?TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'] ?? null;
    }
}
