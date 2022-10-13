..  include:: /Includes.rst.txt

..  _events:

======
Events
======

..  versionadded:: 0.7.0

    `Feature: #10 - Introduce BeforeRenderingEvent and AfterRenderingEvent <https://github.com/CPS-IT/handlebars/pull/10>`__

There are several events available that allow to influence the rendering.
Event listeners must be registered via the service configuration. More
information can be found in the :ref:`official TYPO3 documentation <t3coreapi:EventDispatcherRegistration>`.

..  _before-rendering-event:

BeforeRenderingEvent
====================

This event is triggered directly before the compiled template is rendered along
with the provided data. This allows the data to be manipulated once again before
it is passed to the Renderer.

Example:

::

    # Classes/EventListener/BeforeRenderingListener.php

    namespace Vendor\Extension\EventListener;

    use Fr\Typo3Handlebars\Event\BeforeRenderingEvent;

    class BeforeRenderingListener
    {
        public function modifyRenderData(BeforeRenderingEvent $event): void
        {
            $data = $event->getData();

            // Do anything...

            $event->setData($data);
        }
    }

..  _after-rendering-event:

AfterRenderingEvent
===================

After the Renderer has completely rendered the template using the provided data,
the :php:`AfterRenderingEvent` is triggered. This can be used to subsequently
influence the rendering result.

Example:

::

    # Classes/EventListener/AfterRenderingListener.php

    namespace Vendor\Extension\EventListener;

    use Fr\Typo3Handlebars\Event\AfterRenderingEvent;

    class AfterRenderingListener
    {
        public function modifyRenderedContent(AfterRenderingEvent $event): void
        {
            $content = $event->getContent();

            // Do anything...

            $event->setContent($content);
        }
    }

..  _events-sources:

Sources
=======

..  seealso::

    View the sources on GitHub:

    - `BeforeRenderingEvent <https://github.com/CPS-IT/handlebars/blob/main/Classes/Event/BeforeRenderingEvent.php>`__
    - `AfterRenderingEvent <https://github.com/CPS-IT/handlebars/blob/main/Classes/Event/AfterRenderingEvent.php>`__
