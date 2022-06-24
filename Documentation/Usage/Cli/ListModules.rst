.. include:: ../../Includes.txt

.. _list-modules:

============
List modules
============

.. versionadded:: 0.8.0

   `Feature: #22 - Introduce various console commands <https://github.com/CPS-IT/handlebars/pull/22>`__

Prints all globally registered Handlebars modules. This mainly includes
all data processors that are tagged with `handlebars.processor` and their
related components.

.. _list-modules-usage:

Usage
=====

.. code-block:: bash

   handlebars:list:modules [<name>] [-v|--verbose]

.. _list-modules-arguments:

Arguments
=========

.. _list-modules-arguments-name:
.. container:: table-row

   Property
      name

   Data type
      string

   Description
      Optional name or glob of concrete Handlebars modules to be looked up.

   Examples
      *  *empty string*: No restriction (prints all modules)
      *  :php:`Foo` or :php:`FooProcessor`: Prints :php:`FooProcessor` and
         related components
      *  :php:`Foo*`: Prints all modules starting with :php:`Foo` (case-insensitive)
      *  :php:`*Foo`: Prints all modules ending with :php:`Foo` (case-insensitive)
      *  :php:`*Foo*`: Prints all modules containing with :php:`Foo` (case-insensitive)

.. _list-modules-options:

Options
=======

.. _list-modules-options-verbose:
.. container:: table-row

   Property
      `--verbose`, `-v`

   Data type
      boolean

   Description
      Increase the verbosity of messages to show more information about
      registered modules.

.. _list-modules-examples:

Examples
========

.. _list-modules-examples-all-modules:

**Print all modules:**

.. code-block:: bash

   typo3 handlebars:list:modules

.. _list-modules-examples-filter-modules:

**Filter modules:**

.. code-block:: bash

   typo3 handlebars:list:modules "foo*"

.. _list-modules-examples-verbose-output:

**Verbose output:**

.. code-block:: bash

   typo3 handlebars:list:modules --verbose
