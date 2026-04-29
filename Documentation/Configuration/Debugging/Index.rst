..  include:: /Includes.rst.txt

..  _debugging:

=========
Debugging
=========

..  caution::
    This page is outdated and does not reflect the current state
    of the extension. It will be updated soon.

Rendering of Handlebars templates can be done with additional debugging. This
results in individual tags being provided with debug output, which can be
used to better localize errors, especially during development, and thus fix
them more efficiently.

..  warning::

    Note that debugging only applies in the default `Renderer`. If a custom
    `Renderer` is implemented and used, then this functionality is not available
    out of the box.

..  _typoscript:

TypoScript
==========

..  seealso::

    Read more about this TypoScript configuration in the
    :ref:`official TYPO3 documentation <t3tsref:setup-config-debug>`.

..  code-block:: typoscript

    # Disable debugging
    config.debug = 0

    # Enable debugging
    config.debug = 1

..  _local-configuration:

Local configuration
===================

::

    // Disable debugging
    $GLOBALS['TYPO3_CONF_VARS']['FE']['debug'] = false;

    // Enable debugging
    $GLOBALS['TYPO3_CONF_VARS']['FE']['debug'] = true;
