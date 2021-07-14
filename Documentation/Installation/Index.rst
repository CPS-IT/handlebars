.. include:: ../Includes.txt

.. _installation:

============
Installation
============

.. important::

   **Composer installation required**

   Installations from other sources than Composer (e.g. Extension Manager, TYPO3
   Extension Repository) are currently not supported and might not work as expected.

.. _requirements:

Requirements
============

* PHP 7.1+
* Composer
* TYPO3 10.4 LTS

.. _steps:

Installation
============

Require the extension via Composer:

.. code-block:: bash

   composer require cpsit/typo3-handlebars

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
               'handlebars' => '0.5.0-0.5.99',
           ],
       ],
   ];

Otherwise, template paths are not evaluated in the right order and might get
overridden.
