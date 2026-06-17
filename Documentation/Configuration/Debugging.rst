..  include:: /Includes.rst.txt

..  _debugging:

=========
Debugging
=========

When TYPO3 frontend debug mode is active, the Handlebars renderer emits
additional debug output for individual template tags. This makes it easier
to localize rendering errors during development.

..  note::

    Debug output is only produced by the built-in :php:`HandlebarsRenderer`.
    Custom renderer implementations do not inherit this behaviour unless they
    implement it explicitly.

..  _debugging-typoscript:

Via TypoScript
==============

..  code-block:: typoscript

    # Enable debug mode
    config.debug = 1

    # Disable debug mode
    config.debug = 0

..  seealso::

    :ref:`config.debug <t3tsref:setup-config-debug>` in the TypoScript
    reference.

..  _debugging-system-configuration:

Via system configuration
========================

..  code-block:: php
    :caption: config/system/additional.php

    // Enable debug mode
    $GLOBALS['TYPO3_CONF_VARS']['FE']['debug'] = true;

    // Disable debug mode
    $GLOBALS['TYPO3_CONF_VARS']['FE']['debug'] = false;
