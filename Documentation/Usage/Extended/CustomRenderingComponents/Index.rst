.. include:: /Includes.rst.txt

.. _custom-rendering-components:

===========================
Custom rendering components
===========================

All components are described using interfaces. This makes it easy to
exchange individual components. The following illustrates how such a
use case can look.

.. _custom-renderer:

Custom `Renderer`
=================

The interface :php:`Fr\Typo3Handlebars\Renderer\RendererInterface`
describes a `Renderer`. A distinction must be made as to whether the
custom `Renderer` is to be used for all components or only for individual
variants.

.. important::

   If you want your custom `Renderer` to be autoconfigured with all globally
   registered `Helpers`, make sure to tag it with `handlebars.renderer` and
   implement the interface :php:`Fr\Typo3Handlebars\Renderer\HelperAwareInterface`.

.. _global-replacement:

Global replacement
------------------

If the custom `Renderer` is to be used equally for all components, it can
simply be registered as a global replacement for the default `Renderer` in
the :file:`Services.yaml` file.

.. code-block:: yaml

   # Configuration/Services.yaml

   services:
     Fr\Typo3Handlebars\Renderer\RendererInterface:
       alias: 'Vendor\Extension\Renderer\AlternativeRenderer'

.. warning::

   **Provide necessary dependencies**

   Note that if you use your own `Renderer`, you are responsible for providing
   it with the necessary dependencies. These include the cache, `TemplateResolver`,
   and the default data (if needed). This is already configured with the default
   `Renderer`.

.. _single-replacement:

Single replacement
------------------

A custom `Renderer` can also be used only for specific modules. In this case,
it replaces the default `Renderer` for the concrete `Presenters`.

.. warning::

   **Use of the** `AbstractPresenter` **required**

   The following example is only applicable to `Presenters` that extend the
   `AbstractPresenter`, since it holds the required dependency in its constructor.
   This is not part of the `PresenterInterface`.

.. code-block:: yaml

   # Configuration/Services.yaml

   services:
     Vendor\Extension\Presenter\MyCustomPresenter:
       arguments:
         $renderer: ['@Vendor\Extension\Renderer\AlternativeRenderer']

.. _custom-template-resolver:

Custom `TemplateResolver`
=========================

A standard `TemplateResolver` exists for resolving template paths for templates
and partials. This is used in the default `Renderer`, but a custom
`TemplateResolver` can also be used for specific purposes.

To use a custom `TemplateResolver`, a corresponding class is created that
implements the :php:`Fr\Typo3Handlebars\Renderer\Template\TemplateResolverInterface`
interface:

::

   # Classes/Renderer/Template/AlternativeTemplateResolver.php

   namespace Vendor\Extension\Renderer\Template;

   use Fr\Typo3Handlebars\Renderer\Template\TemplateResolverInterface;

   class AlternativeTemplateResolver implements TemplateResolverInterface
   {
       /**
        * @var string[]
        */
       private array $supportedFileExtensions = ['hbs', 'hbs.html'];

       public function getSupportedFileExtensions(): array
       {
           return $this->supportedFileExtensions;
       }

       public function supports(string $fileExtension): bool
       {
           return in_array(strtolower($fileExtension), $this->supportedFileExtensions, true);
       }

       public function resolveTemplatePath(string $templatePath): string
       {
           // ...
       }
   }

This is then used in the :file:`Services.yaml` file instead of the standard
`TemplateResolver`:

.. code-block:: yaml

   # Configuration/Services.yaml

   services:
     Fr\Typo3Handlebars\Renderer\Template\TemplateResolverInterface:
       alias: 'Vendor\Extension\Renderer\Template\AlternativeTemplateResolver'

.. _custom-rendering-components-sources:

Sources
=======

.. seealso::

   View the sources on GitHub:

   -  `RendererInterface <https://github.com/CPS-IT/handlebars/blob/main/Classes/Renderer/RendererInterface.php>`__
   -  `HelperAwareInterface <https://github.com/CPS-IT/handlebars/blob/main/Classes/Renderer/HelperAwareInterface.php>`__
   -  `TemplateResolverInterface <https://github.com/CPS-IT/handlebars/blob/main/Classes/Renderer/Template/TemplateResolverInterface.php>`__
