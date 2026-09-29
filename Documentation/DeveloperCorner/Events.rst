..  include:: /Includes.rst.txt

..  _events:

======
Events
======

The extension dispatches three PSR-14 events during the rendering pipeline.

..  contents::
    :local:
    :depth: 1

..  _before-template-compilation-event:

BeforeTemplateCompilationEvent
================================

Dispatched immediately before a template is compiled. The event provides
read-only access to the rendering context and the renderer. Use it to
inspect the context (e.g., to log which template is about to be rendered)
or to trigger additional rendering via the renderer:

..  code-block:: php
    :caption: EXT:my_extension/Classes/EventListener/BeforeTemplateCompilationListener.php

    namespace Vendor\Extension\EventListener;

    use CPSIT\Typo3Handlebars\Event\BeforeTemplateCompilationEvent;

    final readonly class BeforeTemplateCompilationListener
    {
        public function __invoke(BeforeTemplateCompilationEvent $event): void
        {
            $context = $event->getContext();   // RenderingContext
            $renderer = $event->getRenderer(); // Renderer
        }
    }

..  _before-rendering-event:

BeforeRenderingEvent
=====================

Dispatched after variables have been resolved and merged, immediately before
the compiled template is executed. The full variable set can be read and
modified:

..  code-block:: php
    :caption: EXT:my_extension/Classes/EventListener/BeforeRenderingListener.php

    namespace Vendor\Extension\EventListener;

    use CPSIT\Typo3Handlebars\Event\BeforeRenderingEvent;

    final readonly class BeforeRenderingListener
    {
        public function __invoke(BeforeRenderingEvent $event): void
        {
            // Add a variable
            $event->addVariable('timestamp', time());

            // Replace the entire variable set
            $variables = $event->getVariables();
            $variables['title'] = strtoupper($variables['title'] ?? '');
            $event->setVariables($variables);

            // Remove a variable
            $event->removeVariable('internalFlag');
        }
    }

Available methods:

*   :php:`getVariables()` / :php:`setVariables(array $variables)` — get or
    replace the full variable set
*   :php:`addVariable(string $name, mixed $value)` — add or overwrite a single
    variable
*   :php:`removeVariable(string $name)` — remove a variable by name
*   :php:`getContext()` — the :php:`RenderingContext` (read-only)
*   :php:`getRenderer()` — the :php:`Renderer` (read-only)

..  _after-rendering-event:

AfterRenderingEvent
====================

Dispatched after the template has been fully rendered. The rendered HTML
string can be read and replaced:

..  code-block:: php
    :caption: EXT:my_extension/Classes/EventListener/AfterRenderingListener.php

    namespace Vendor\Extension\EventListener;

    use CPSIT\Typo3Handlebars\Event\AfterRenderingEvent;

    final readonly class AfterRenderingListener
    {
        public function __invoke(AfterRenderingEvent $event): void
        {
            $content = $event->getContent();
            $event->setContent(trim($content));
        }
    }

Available methods:

*   :php:`getContent()` / :php:`setContent(string $content)` — get or replace
    the rendered output
*   :php:`getContext()` — the :php:`RenderingContext` (read-only)
*   :php:`getRenderer()` — the :php:`Renderer` (read-only)

..  seealso::

    *   `BeforeTemplateCompilationEvent <https://github.com/CPS-IT/handlebars/blob/main/Classes/Event/BeforeTemplateCompilationEvent.php>`__
    *   `BeforeRenderingEvent <https://github.com/CPS-IT/handlebars/blob/main/Classes/Event/BeforeRenderingEvent.php>`__
    *   `AfterRenderingEvent <https://github.com/CPS-IT/handlebars/blob/main/Classes/Event/AfterRenderingEvent.php>`__
