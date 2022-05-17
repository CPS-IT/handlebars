.. include:: /Includes.rst.txt

.. _shared-components:

=================
Shared components
=================

It is not always necessary or desired to automatically register all
related components based on the registration of a `DataProcessor`.
In some cases, for example, it may be necessary to use a component
more than once, e.g. if several modules use the same template and
thus one `Presenter` can be used for all those modules.

To be able to cover this special case, it is possible to specify
a concrete `DataProvider` or `Presenter` for individual `DataProcessors`
in the :file:`Services.yaml` file.

.. warning::

   **Use of the** `AbstractDataProcessor` **required**

   The following examples are only applicable to `DataProcessors` that
   extend the `AbstractDataProcessor`, since it provides the necessary
   methods. These are not part of the `DataProcessorInterface`.

.. _example-1-shared-presenter:

Example 1: Shared `Presenter`
=============================

Assume that there are two modules *Highlight Box* and *Highlight Text*,
which are both rendered using the same Handlebars template. The data
provision is still done via two separate `DataProviders`.

In the :file:`Services.yaml` file, we register both `DataProcessors`, but
specify a concrete method call :php:`setPresenter()`. This is normally
called automatically if it is not set manually.

.. code-block:: yaml

   # Configuration/Services.yaml

   services:
     Vendor\Extension\DataProcessing\HighlightBoxProcessor:
       tags: ['handlebars.processor']
       calls:
         - setPresenter: ['@Vendor\Extension\Presenter\HighlightPresenter']
     Vendor\Extension\DataProcessing\HighlightTextProcessor:
       tags: ['handlebars.processor']
       calls:
         - setPresenter: ['@Vendor\Extension\Presenter\HighlightPresenter']

Both `DataProcessors` are now injected with the same `Presenter`, while all
other components continue to act independently.

.. _example-2-shared-data-provider:

Example 2: Shared `DataProvider`
================================

The same procedure can be used if a common `DataProvider` is to be used instead
of a common `Presenter`. In this case the method call must be :php:`setProvider()`:

.. code-block:: yaml

   # Configuration/Services.yaml

   services:
     Vendor\Extension\DataProcessing\HighlightBoxProcessor:
       tags: ['handlebars.processor']
       calls:
         - setProvider: ['@Vendor\Extension\Data\HighlightProvider']
     Vendor\Extension\DataProcessing\HighlightTextProcessor:
       tags: ['handlebars.processor']
       calls:
         - setProvider: ['@Vendor\Extension\Data\HighlightProvider']

.. _shared-components-sources:

Sources
=======

.. seealso::

   View the sources on GitHub:

   -  `AbstractDataProcessor <https://github.com/CPS-IT/handlebars/blob/main/Classes/DataProcessing/AbstractDataProcessor.php>`__
   -  `DataProcessorPass <https://github.com/CPS-IT/handlebars/blob/main/Classes/DependencyInjection/DataProcessorPass.php>`__
   -  `ProcessingBridge <https://github.com/CPS-IT/handlebars/blob/main/Classes/DependencyInjection/ProcessingBridge.php>`__
