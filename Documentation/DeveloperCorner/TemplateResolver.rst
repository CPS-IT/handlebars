..  include:: /Includes.rst.txt

..  _developer-corner-template-resolver:

================
TemplateResolver
================

Implement the
:php:interface:`CPSIT\\Typo3Handlebars\\Renderer\\Template\\TemplateResolver`
interface to change how template and partial names are resolved to absolute file
paths — for example to support a different directory layout, an additional file
extension, or a database-driven path lookup.

The extension ships two implementations:
:php:`CPSIT\Typo3Handlebars\Renderer\Template\FlatTemplateResolver` (the
default) and
:php:`CPSIT\Typo3Handlebars\Renderer\Template\HandlebarsTemplateResolver`.

:php:`CPSIT\Typo3Handlebars\Renderer\Template\BaseTemplateResolver` implements
:php:`supports()` and provides protected helpers for normalizing root paths and
resolving filenames with :typoscript:`EXT:` syntax. Extending it keeps
implementations concise.

..  php:namespace:: CPSIT\Typo3Handlebars\Renderer\Template

..  php:interface:: TemplateResolver

    ..  php:method:: supports(string $fileExtension)

        Return :php:`true` if this resolver handles the given file extension.

        :param string $fileExtension: File extension without leading dot.
        :returntype: bool

    ..  php:method:: resolveTemplatePath(string $templatePath, ?string $format = null)

        Resolve a template name or relative path to its absolute file path.

        :param string $templatePath: Template name or relative path.
        :param string|null $format: Optional file extension override.
        :returntype: string

    ..  php:method:: resolvePartialPath(string $partialPath, ?string $format = null)

        Resolve a partial name or relative path to its absolute file path.

        :param string $partialPath: Partial name or relative path.
        :param string|null $format: Optional file extension override.
        :returntype: string

..  _developer-corner-template-resolver-flat:

FlatTemplateResolver
====================

:php:`FlatTemplateResolver` is the default implementation. It scans all
configured root paths recursively and builds an in-memory map of every
template file, keyed by its bare filename (without directory). A lookup
therefore succeeds regardless of where in the directory tree the file lives.

Template and partial names must be prefixed with :file:`@` to trigger flat
resolution. A name without the prefix is passed directly to
:php:`HandlebarsTemplateResolver` (see below).

..  code-block:: typoscript
    :caption: Referencing a flat template in TypoScript

    tt_content.tx_myext_teaser = HANDLEBARSTEMPLATE
    tt_content.tx_myext_teaser {
        templateName = @teaser
    }

..  code-block:: handlebars
    :caption: Referencing a flat partial in a Handlebars template

    {{> @card}}

**Variant separator**

Appending :file:`--<variant>` to an :file:`@`-prefixed name selects a variant
of a component. If no file with that exact name exists, the resolver automatically
falls back to the base name:

..  code-block:: handlebars

    {{> @card--highlighted}}   {{!-- falls back to @card if not found --}}

This convention follows `Fractal's naming rules
<https://fractal.build/guide/core-concepts/naming.html>`__.

**File precedence**

When the same filename exists under multiple root paths, the higher-priority
root path wins (see :ref:`template-paths`). Within a single root path, files
are sorted by name and the first occurrence is used, matching Fractal's
uniqueness guarantee.

..  _developer-corner-template-resolver-handlebars:

HandlebarsTemplateResolver
==========================

:php:`HandlebarsTemplateResolver` resolves template and partial names as
paths relative to the configured root paths. Given the name :file:`Blog/List`,
it searches each root path (highest priority first) for a matching file —
for example :file:`Blog/List.hbs`.

This resolver is used as the fallback inside :php:`FlatTemplateResolver`
for any name that does not start with ``@``, so both resolution strategies
are active at the same time.

..  _developer-corner-template-resolver-example:

Example implementation
======================

..  code-block:: php
    :caption: EXT:my_extension/Classes/Renderer/Template/MyTemplateResolver.php

    namespace Vendor\Extension\Renderer\Template;

    use CPSIT\Typo3Handlebars\Exception;
    use CPSIT\Typo3Handlebars\Renderer\Template\BaseTemplateResolver;
    use CPSIT\Typo3Handlebars\Renderer\Template\TemplatePaths;

    final readonly class MyTemplateResolver extends BaseTemplateResolver
    {
        public function __construct(
            private TemplatePaths $templatePaths,
        ) {}

        public function resolveTemplatePath(string $templatePath, ?string $format = null): string
        {
            [$templateRootPaths] = $this->resolveTemplatePaths($this->templatePaths);

            foreach (array_reverse($templateRootPaths) as $rootPath) {
                $filename = $this->resolveFilename($templatePath, $rootPath, $format ?? 'hbs');

                if (is_file($filename)) {
                    return $filename;
                }
            }

            throw new Exception\TemplatePathIsNotResolvable($templatePath);
        }

        public function resolvePartialPath(string $partialPath, ?string $format = null): string
        {
            [, $partialRootPaths] = $this->resolveTemplatePaths($this->templatePaths);

            foreach (array_reverse($partialRootPaths) as $rootPath) {
                $filename = $this->resolveFilename($partialPath, $rootPath, $format ?? 'hbs');

                if (is_file($filename)) {
                    return $filename;
                }
            }

            throw new Exception\PartialPathIsNotResolvable($partialPath);
        }
    }

..  _developer-corner-template-resolver-wire:

Wiring the implementation
=========================

Register the custom resolver as the implementation of the
:php:`TemplateResolver` interface in your extension's :file:`Services.yaml`:

..  code-block:: yaml
    :caption: Configuration/Services.yaml

    services:
      CPSIT\Typo3Handlebars\Renderer\Template\TemplateResolver:
        alias: Vendor\Extension\Renderer\Template\MyTemplateResolver
