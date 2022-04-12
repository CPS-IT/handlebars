.. include:: ../../../Includes.txt

.. _using-the-content-object-renderer:

=================================
Using the `ContentObjectRenderer`
=================================

In some cases, the presence of the current `ContentObjectRenderer` may
be necessary in the `DataProvider`. For this case an interface
:php:`Fr\Typo3Handlebars\ContentObjectRendererAwareInterface` is
provided, which can be used in combination with the trait
:php:`Fr\Typo3Handlebars\Traits\ContentObjectRendererAwareTrait`.

.. _content-object-renderer-usage:

Usage
=====

.. rst-class:: bignums-xxl

#. Transfer of the `ContentObjectRenderer`

   If the rendering process is triggered via TypoScript, the
   `DataProcessor` is automatically assigned the current instance of
   the `ContentObjectRenderer` (via the :php:`cObj` property). It can
   then pass this to the `DataProvider`:

   ::

      # Classes/DataProcessing/CustomProcessor.php

      namespace Vendor\Extension\DataProcessing;

      use Fr\Typo3Handlebars\DataProcessing\AbstractDataProcessor;

      class CustomProcessor extends AbstractDataProcessor
      {
          protected function render(): string
          {
              $this->provider->setContentObjectRenderer($this->cObj);
              // ...
          }
      }

#. Assure `ContentObjectRenderer` is available

   In the `DataProvider`, the existence of the `ContentObjectRenderer`
   can be easily checked if the associated trait is used:

   ::

      # Classes/Data/CustomProvider.php

      namespace Vendor\Extension\Data;

      use Fr\Typo3Handlebars\ContentObjectRendererAwareInterface;
      use Fr\Typo3Handlebars\Data\DataProviderInterface;
      use Fr\Typo3Handlebars\Data\Response\ProviderResponseInterface;
      use Fr\Typo3Handlebars\Traits\ContentObjectRendererAwareTrait;

      class CustomProvider implements DataProviderInterface, ContentObjectRendererAwareInterface
      {
          use ContentObjectRendererAwareTrait;

          public function get(array $data): ProviderResponseInterface
          {
              $this->assertContentObjectRendererIsAvailable();
              // ...
          }
      }

#. Use the `ContentObjectRenderer`

   If successful, the `ContentObjectRenderer` can then be used, for example,
   to parse database content generated using RTE:

   .. code-block:: diff

       # Classes/Data/CustomProvider.php

       namespace Vendor\Extension\Data;

       use Fr\Typo3Handlebars\ContentObjectRendererAwareInterface;
       use Fr\Typo3Handlebars\Data\DataProviderInterface;
       use Fr\Typo3Handlebars\Data\Response\ProviderResponseInterface;
       use Fr\Typo3Handlebars\Traits\ContentObjectRendererAwareTrait;
      +use Vendor\Extension\Data\Response\CustomProviderResponse;

       class CustomProvider implements DataProviderInterface, ContentObjectRendererAwareInterface
       {
           use ContentObjectRendererAwareTrait;

           public function get(array $data): ProviderResponseInterface
           {
               $this->assertContentObjectRendererIsAvailable();
      -        // ...
      +
      +        $text = $this->parseText($data);
      +
      +        return new CustomProviderResponse($text);
          }
      +
      +    private function parseText(string $plaintext): string
      +    {
      +        return $this->contentObjectRenderer->parseFunc($plaintext, [], '< lib.parseFunc_RTE');
      +    }
       }

.. _content-object-renderer-sources:

Sources
=======

.. seealso::

   View the sources on GitHub:

   -  `ContentObjectRendererAwareInterface <https://github.com/CPS-IT/handlebars/blob/main/Classes/ContentObjectRendererAwareInterface.php>`__
   -  `ContentObjectRendererAwareTrait <https://github.com/CPS-IT/handlebars/blob/main/Classes/Traits/ContentObjectRendererAwareTrait.php>`__
