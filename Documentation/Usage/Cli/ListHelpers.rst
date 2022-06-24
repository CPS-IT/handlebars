.. include:: ../../Includes.txt

.. _list-helpers:

============
List helpers
============

.. versionadded:: 0.8.0

   `Feature: #22 - Introduce various console commands <https://github.com/CPS-IT/handlebars/pull/22>`__

Prints all registered helpers of Helper-aware Handlebars renders that are
tagged with `handlebars.renderer`.

.. _list-helpers-usage:

Usage
=====

.. code-block:: bash

   handlebars:list:helpers

.. _list-helpers-example:

Example
=======

.. code-block:: bash

   typo3 handlebars:list:helpers
