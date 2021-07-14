.. include:: ../../Includes.txt

.. _basic-usage:

===========
Basic usage
===========

This page shows how to create a single module and which components are
necessary to process data from TYPO3, prepare it and finally output it
in the frontend using a Handlebars template.

The CType `header` serves as an example.

.. note::

   This example only describes the standard process for creating a new
   module. Further possibilities are described in the section
   :ref:`extended-usage`.

.. _example:

Example
=======

.. note::

   All examples are written in PHP 7.4.

.. rst-class:: bignums-xxl

#. Preparations

   Before the actual components are created, some preliminary work is
   necessary.

   .. rst-class:: bignums

   #. Installation

      :ref:`Install <installation>` the extension using Composer.

   #. Define dependencies

      Add the extension as dependency to your extension as described
      :ref:`here <define-dependencies>`.

   #. `Services.yaml`

      Create a basic :file:`Services.yaml` file in your extension. Make sure
      to read and follow the guidelines described at
      :ref:`Dependency injection <t3coreapi:dependency-injection>`.

   #. TypoScript configuration

      Create a TypoScript configuration file and include it in your site's root
      template. Make sure to include static TypoScript from
      EXT:fluid_styled_content.

#. Create a new `DataProcessor`

   Each `DataProcessor` must implement :php:`Fr\Typo3Handlebars\DataProcessing\DataProcessorInterface`.

   There's already a default `DataProcessor` in place that provides some
   basic logic and is required in case you want to develop components
   like described on this page. Just extend your `DataProcessor` from
   :php:`Fr\Typo3Handlebars\DataProcessing\AbstractDataProcessor` and
   implement the abstract method :php:`render()`:

   ::

      # Classes/DataProcessing/HeaderProcessor.php

      namespace Vendor\Extension\DataProcessing;

      use Fr\Typo3Handlebars\DataProcessing\AbstractDataProcessor;

      class HeaderProcessor extends AbstractDataProcessor
      {
          protected function render(): string
          {
              $data = $this->provider->get($this->cObj->data);
              return $this->presenter->present($data);
          }
      }

   .. attention::

      **Use the correct namespace**

      It is very important to create the `DataProcessor` with the correct
      namespace, as all other components will be automatically registered
      based on it.

#. Register `DataProcessor` as service

   The `HeaderProcessor` must now be registered in the :file:`Services.yaml`
   in the next step:

   .. code-block:: yaml

      # Configuration/Services.yaml

      services:
        Vendor\Extension\DataProcessing\HeaderProcessor:
          tags: ['handlebars.processor']

   All related components (`DataProvider`, `Presenter`) are now automatically
   assigned to this `DataProcessor` and registered accordingly.

   .. tip::

      If all `DataProcessors` share the same configuration, they can also be
      registered all at once with the following configuration:

      .. code-block:: yaml

         # Configuration/Services.yaml

         services:
           Vendor\Extension\DataProcessing\:
             resource: '../Classes/DataProcessing/**/*Processor.php'
             tags: ['handlebars.processor']

#. Create a new `DataProvider`

   Next, a `DataProvider` must be created that prepares the module's data and makes
   it available to the `DataProcessor` again. Each `DataProvider` must implement
   :php:`Fr\Typo3Handlebars\Data\DataProviderInterface`.

   ::

      # Classes/Data/HeaderProvider.php

      namespace Vendor\Extension\Data;

      use Fr\Typo3Handlebars\Data\DataProviderInterface;
      use Fr\Typo3Handlebars\Data\Response\ProviderResponseInterface;
      use Vendor\Extension\Data\Response\HeaderProviderResponse;

      class HeaderProvider implements DataProviderInterface
      {
          public function get(array $data): ProviderResponseInterface
          {
              return (new HeaderProviderResponse($data['header']))
                  ->setHeaderLayout((int)$data['header_layout'])
                  ->setHeaderLink($data['header_link'])
                  ->setSubheader($data['subheader']);
          }
      }

   As you can see, the `DataProvider` returns an instance of a so-called
   `ProviderResponse` object. This holds the prepared data for higher-level transfer
   within the rendering process. Create it in the associated namespace:

   ::

      # Classes/Data/Response/HeaderProviderResponse.php

      namespace Vendor\Extension\Data\Response;

      use Fr\Typo3Handlebars\Data\Response\ProviderResponseInterface;

      class HeaderProviderResponse implements ProviderResponseInterface
      {
          public const LAYOUT_DEFAULT = 0;

          private string $header;
          private string $headerLayout = '';
          private int $headerLink = self::LAYOUT_DEFAULT;
          private string $subheader = '';

          public function __construct(string $header)
          {
              $this->header = $header;
              $this->validate();
          }

          // Getters and setters...

          public function toArray(): array
          {
              return [
                  'header' => $this->header,
                  'headerLayout' => $this->headerLayout,
                  'headerLink' => $this->headerLink,
                  'subheader' => $this->subheader,
              ];
          }

          private function validate(): void
          {
              if ('' === trim($this->header)) {
                  throw new \InvalidArgumentException('Header must not be empty.', 1626108393);
              }
          }
      }

#. Create a new `Presenter`

   To complete the rendering process, a new `Presenter` called `HeaderPresenter`
   must be created. It must implement the :php:`Fr\Typo3Handlebars\Presenter\PresenterInterface`;
   furthermore, an :php:`Fr\Typo3Handlebars\Presenter\AbstractPresenter` is already
   available with the default `Renderer` already specified as a dependency.

   ::

      # Classes/Presenter/HeaderPresenter.php

      namespace Vendor\Extension\Presenter;

      use Fr\Typo3Handlebars\Data\Response\ProviderResponseInterface;
      use Fr\Typo3Handlebars\Exception\UnableToPresentException;
      use Fr\Typo3Handlebars\Presenter\AbstractPresenter;

      class HeaderPresenter extends AbstractPresenter
      {
          public function present(ProviderResponseInterface $data): string
          {
              if (!($data instanceof HeaderProviderResponse)) {
                  throw new UnableToPresentException(
                      'Received unexpected response from provider.',
                      1613552315
                  );
              }

              // Use data from ProviderResponse or implement custom logic
              $renderData = $data->toArray();

              return $this->renderer->render(
                  'Extensions/FluidStyledContent/Header',
                  $renderData
              );
          }
      }

#. Set up TypoScript configuration

   Finally, you have to configure via TypoScript that instead of the default Fluid
   rendering the special Handlebars rendering should be executed for all content
   elements of the CType `header`.

   For this purpose, each `DataProcessor` provides a method
   :php:`process(string $content, array $configuration)` as entry point.

   .. code-block:: typoscript

      # Configuration/TypoScript/setup.typoscript

      tt_content.header = USER
      tt_content.header.userFunc = Vendor\Extension\DataProcessing\HeaderProcessor->process

#. Optional: Create custom `Helpers`

   If your templates use custom `Helpers`, you will need to create them
   additionally. Read :ref:`create-a-custom-helper` to learn what options are
   available for creating your own `Helpers`.

#. Flush caches

   Changes were made to the service configuration and also the rendering was
   overwritten using TypoScript. Therefore, it is now necessary to ensure that
   the **caches are flushed and the service container is reconfigured**.

.. _basic-usage-sources:

Sources
=======

.. seealso::

   View the sources on GitHub:

   -  `DataProcessorInterface <https://github.com/CPS-IT/handlebars/blob/master/Classes/DataProcessing/DataProcessorInterface.php>`__
   -  `AbstractDataProcessor <https://github.com/CPS-IT/handlebars/blob/master/Classes/DataProcessing/AbstractDataProcessor.php>`__
   -  `DataProviderInterface <https://github.com/CPS-IT/handlebars/blob/master/Classes/Data/DataProviderInterface.php>`__
   -  `ProviderResponseInterface <https://github.com/CPS-IT/handlebars/blob/master/Classes/Data/Response/ProviderResponseInterface.php>`__
   -  `PresenterInterface <https://github.com/CPS-IT/handlebars/blob/master/Classes/Presenter/PresenterInterface.php>`__
   -  `AbstractPresenter <https://github.com/CPS-IT/handlebars/blob/master/Classes/Presenter/AbstractPresenter.php>`__
