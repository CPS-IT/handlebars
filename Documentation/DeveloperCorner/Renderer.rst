..  include:: /Includes.rst.txt

..  _developer-corner-renderer:

========
Renderer
========

Implement the :php:interface:`CPSIT\\Typo3Handlebars\\Renderer\\Renderer`
interface to replace the entire rendering stack — for example to use a
different template engine, add a pre-render transformation, or wrap the
compiled output. The default implementation is
:php:`CPSIT\Typo3Handlebars\Renderer\HandlebarsRenderer`.

..  php:namespace:: CPSIT\Typo3Handlebars\Renderer

..  php:interface:: Renderer

    ..  php:method:: renderTemplate(RenderingContext $context)

        Compile and render a template. The :php:`RenderingContext` carries the
        template path or inline source, the current variable set, and the PSR-7
        request.

        :param RenderingContext $context: The current rendering context.
        :returntype: string

    ..  php:method:: renderPartial(RenderingContext $context)

        Compile and render a partial.

        :param RenderingContext $context: The current rendering context.
        :returntype: string

..  _developer-corner-renderer-wire:

Wiring the implementation
=========================

Register the custom renderer as the implementation of the
:php:`Renderer` interface in your extension's :file:`Services.yaml`:

..  code-block:: yaml
    :caption: Configuration/Services.yaml

    services:
      CPSIT\Typo3Handlebars\Renderer\Renderer:
        alias: Vendor\Extension\Renderer\MyRenderer
