.. include:: ../../Includes.txt

.. _template-paths:

==============
Template paths
==============

There exist several ways to declare template root paths and partial root
paths. The most relevant ones are described below.

.. _configuration-via-service-container:

Configuration via service container
===================================

.. attention::

   Make sure you have defined :ref:`EXT:handlebars as dependency <define-dependencies>`
   in your extension(s). Otherwise, template root paths might not be interpreted correctly.

The easiest way to register your template root paths and partial root paths
is by using the :file:`Services.yaml` file:

.. code-block:: yaml

   # Configuration/Services.yaml

   handlebars:
     template:
       template_root_paths:
         10: EXT:my_extension/Resources/Private/Templates
       partial_root_paths:
         10: EXT:my_extension/Resources/Private/Partials

The `HandlebarsExtension`_
takes care of all template paths. They will be merged and then added to the
service container resulting in the following parameters:

* `%handlebars.template_root_paths%`
* `%handlebars.partial_root_paths%`

You can reference those parameters in your custom configuration to use the
resolved template paths in your services.

The drawback of this configuration is that it is applied to the whole TYPO3
instance since there exists only one service container for the whole system.
In case you need different template paths for specific parts of your
installation, take a look at the following configuration method that uses
TypoScript.

.. _configuration-via-typoscript:

Configuration via TypoScript
============================

.. versionadded:: 0.7.0

   `Feature: #15 - Allow TypoScript as configuration for template paths <https://github.com/CPS-IT/handlebars/pull/15>`__

A more flexible configuration method is the usage of TypoScript. This way you
can override the configuration from the service container (as described above)
which allows you to define different template root paths and partial root
paths for specific parts of the system.

.. code-block:: typoscript

   plugin.tx_handlebars {
     view {
       templateRootPaths {
         20 = EXT:my_other_extension/Resources/Private/Templates
       }
       partialRootPaths {
         20 = EXT:my_other_extension/Resources/Private/Partials
       }
     }
   }

.. note::

   **Configuration of template paths in multiple extensions**

   If template paths are defined multiple times (e.g. within various
   extensions that provide Handlebars templates), the ones with the
   same numeric key will be overridden by the last defined one.

.. _HandlebarsExtension: https://github.com/CPS-IT/handlebars/blob/master/Classes/DependencyInjection/Extension/HandlebarsExtension.php
