..  include:: /Includes.rst.txt

..  _extbase-repositories:

====================
Extbase repositories
====================

..  versionadded:: 0.7.3

    `Feature: #23 - Provide compatibility method for Extbase repositories <https://github.com/CPS-IT/handlebars/pull/23>`__

..  warning::

    **Use of the** `AbstractDataProcessor` **required**

    This compatibility method is only applicable to `DataProcessors`
    that extend the :php:`AbstractDataProcessor`, since it provides the
    necessary method. It is not part of the :php:`DataProcessorInterface`.

When Extbase repositories are used to fetch data via the `DataProvider`,
it may be necessary to perform the necessary bootstrapping for Extbase
repositories. This is the case whenever the rendering process is executed
outside the Extbase context and fields such as `tt_content.pages` or
`tt_content.recursive` are to be accessed in the repository to determine
the storage PIDs.

To execute the necessary bootstrapping or to reset the underlying
:php:`ConfigurationManager` and to fill it with the current
:php:`ContentObjectRenderer`, the method
:php:`initializeConfigurationManager()` must be executed in the
`DataProcessor`.

..  _extbase-repositories-usage:

Usage
=====

..  code-block:: diff

     # Classes/DataProcessing/HeaderProcessor.php

     namespace Vendor\Extension\DataProcessing;

     use Fr\Typo3Handlebars\DataProcessing\AbstractDataProcessor;

     class HeaderProcessor extends AbstractDataProcessor
     {
         protected function render(): string
         {
    +        $this->initializeConfigurationManager();
             $data = $this->provider->get($this->cObj->data);
             return $this->presenter->present($data);
         }
     }

..  _extbase-repositories-sources:

Sources
=======

..  seealso::

    View the sources on GitHub:

    - `AbstractDataProcessor <https://github.com/CPS-IT/handlebars/blob/main/Classes/DataProcessing/AbstractDataProcessor.php>`__
