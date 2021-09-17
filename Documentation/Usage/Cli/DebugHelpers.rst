.. include:: ../../Includes.txt

.. _debug-helpers:

=============
Debug helpers
=============

Prints all registered helpers of Helper-aware Handlebars renders that are
tagged with `handlebars.renderer`.

.. _debug-helpers-usage:

Usage
=====

.. code-block:: bash

   handlebars:debug:helpers

.. _debug-helpers-example:

Example
=======

.. code-block:: none

   $ typo3 handlebars:debug:helpers

   Renderer: handlebars.renderer
   -----------------------------

   Renderer class: Fr\Typo3Handlebars\Renderer\HandlebarsRenderer

    ------------------------ ----------------------------------------------------------------
     Name                     Callable
    ------------------------ ----------------------------------------------------------------
     baz                      Vendor\Extension\Renderer\Helper\BazHelper::evaluate
     foo                      Vendor\Extension\Renderer\Helper\FooHelper::evaluate
    ------------------------ ----------------------------------------------------------------
