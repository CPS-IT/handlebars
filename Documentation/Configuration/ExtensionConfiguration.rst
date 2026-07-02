..  include:: /Includes.rst.txt

..  _extension-configuration:

=======================
Extension configuration
=======================

The extension provides configuration options via TYPO3's extension
configuration. These are mapped onto typed configuration classes and
are available for autowiring as
:php:`CPSIT\Typo3Handlebars\Configuration\HandlebarsConfiguration`.

..  contents:: Properties
    :local:
    :depth: 1

----

..  _extension-configuration-rendering-strict-mode:

rendering.strictMode
====================

:aspect:`Type`
    boolean

:aspect:`Default`
    :php:`false`

:aspect:`Description`
    Enables strict mode for the Handlebars renderer. In strict mode, the
    compiled template throws an exception as soon as it encounters a
    variable, property, or path that does not exist, instead of silently
    rendering an empty string. This is useful during development to catch
    typos in variable names and template paths early.

..  seealso::

    :ref:`migration-1-debug-mode` for context on why this setting replaces
    the previous, debug-mode-triggered strict compilation.
