.. include:: ../../Includes.txt

.. _template-paths:

==============
Template paths
==============

.. attention::

   Make sure you have defined :ref:`EXT:handlebars as dependency <define-dependencies>`
   in your extension(s). Otherwise, template root paths might not be interpreted correctly.

Registration of template root paths and partial root paths is done within
the :file:`Services.yaml` file:

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

.. note::

   **Configuration of template paths in multiple extensions**

   If template paths are defined multiple times (e.g. within various
   extensions that provide Handlebars templates), the ones with the
   same numeric key will be overridden by the last defined one.

.. _HandlebarsExtension: https://github.com/CPS-IT/handlebars/blob/master/Classes/DependencyInjection/Extension/HandlebarsExtension.php
