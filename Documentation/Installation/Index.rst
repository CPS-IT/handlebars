.. include:: ../Includes.txt

.. _installation:

============
Installation
============

.. _requirements:

Requirements
============

* PHP 7.1 - 8.1
* TYPO3 10.4 LTS - 11.5 LTS

.. _steps:

Installation
============

Require the extension via Composer (recommended):

.. code-block:: bash

   composer require cpsit/typo3-handlebars

Or download it from
`TYPO3 extension repository <https://extensions.typo3.org/extension/handlebars>`__.

.. _define-dependencies:

Define dependencies
-------------------

.. attention::

   This is an essential step to ensure service configuration is interpreted
   correctly.

Each extension that depends on EXT:handlebars needs to explicitly define it as
dependency in the appropriate :file:`ext_emconf.php` file:

::

   # ext_emconf.php

   $EM_CONF[$_EXTKEY] = [
       'constraints' => [
           'depends' => [
               'handlebars' => '0.7.0-0.7.99',
           ],
       ],
   ];

Otherwise, template paths are not evaluated in the right order and might get
overridden.
