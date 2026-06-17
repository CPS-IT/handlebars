..  include:: /Includes.rst.txt

..  _developer-corner-template-resolver:

================
TemplateResolver
================

Implement the
:php:interface:`CPSIT\\Typo3Handlebars\\Renderer\\Template\\TemplateResolver`
interface to change how template and partial names are resolved to absolute file
paths — for example to support a different directory layout, an additional file
extension, or a database-driven path lookup. The default implementation is
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
