.. include:: ../../Includes.txt

.. _debug-modules:

=============
Debug modules
=============

Prints all globally registered Handlebars modules. This mainly includes
all data processors that are tagged with `handlebars.processor` and their
related components.

.. _debug-modules-usage:

Usage
=====

.. code-block:: bash

   handlebars:debug:modules [<name>] [-v|--verbose]

.. _debug-modules-example:

Example
=======

.. _debug-modules-example-all-modules:

All modules
-----------

.. code-block:: none

   $ typo3 handlebars:debug:modules

    ------------------------ ----------------------------------------------------------------
     Name                     Declaring class
    ------------------------ ----------------------------------------------------------------
     Baz                      Vendor\Extension\DataProcessing\BazProcessor
     Foo                      Vendor\Extension\DataProcessing\FooProcessor
    ------------------------ ----------------------------------------------------------------

.. _debug-modules-example-filter-modules:

Filter modules
--------------

.. code-block:: none

   $ typo3 handlebars:debug:modules "foo*"

    ------------------------ ----------------------------------------------------------------
     Name                     Declaring class
    ------------------------ ----------------------------------------------------------------
     Foo                      Vendor\Extension\DataProcessing\FooProcessor
    ------------------------ ----------------------------------------------------------------

.. _debug-modules-example-verbose-output:

Verbose output
--------------

.. code-block:: none

   $ typo3 handlebars:debug:modules --verbose

   Baz
   ---

    * DataProcessor: Vendor\Extension\DataProcessing\BazProcessor
    * DataProvider: Vendor\Extension\Data\BazProvider
    * Presenter: Vendor\Extension\Presenter\BazPresenter

   Foo
   ---

    * DataProcessor: Vendor\Extension\DataProcessing\FooProcessor
    * DataProvider: Vendor\Extension\Data\FooProvider
    * Presenter: Vendor\Extension\Presenter\FooPresenter

.. _debug-modules-arguments:

Arguments
=========

.. _label-argumentName:
.. rst-class:: dl-parameters

name
   :sep:`|` :aspect:`Condition:` optional
   :sep:`|` :aspect:`Type:` string
   :sep:`|` :aspect:`Default:` :php:`''`
   :sep:`|`

   Optional name or glob of a concrete Handlebars module to be looked up.

   **Examples:**

   *  :php:`''` (empty string): No restriction (prints all modules)
   *  :php:`Foo` or :php:`FooProcessor`: Prints :php:`FooProcessor` and
      related components
   *  :php:`Foo*`: Prints all modules starting with :php:`Foo` (case-insensitive)

.. _debug-modules-options:

Options
=======

.. _label-optionVerbose:
.. rst-class:: dl-parameters

`--verbose`
   :sep:`|` :aspect:`Shorthand:` `-v`
   :sep:`|` :aspect:`Condition:` optional
   :sep:`|` :aspect:`Type:` boolean
   :sep:`|` :aspect:`Default:` :php:`false`
   :sep:`|`

   Increase the verbosity of messages to show more information about
   registered modules.
