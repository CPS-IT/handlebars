..  include:: /Includes.rst.txt

..  _variables:

=========
Variables
=========

Template variables are available at two scopes: globally for every rendering,
and locally for a single content object rendering.

..  contents::
    :local:
    :depth: 1

..  _variables-global:

Global variables
================

Global variables are merged into every template rendering automatically.
They can be defined through TypoScript or the service container.

..  _variables-global-typoscript:

Via TypoScript
--------------

Use :typoscript:`plugin.tx_handlebars.variables` to define variables available
on every page where the TypoScript is active:

..  code-block:: typoscript

    plugin.tx_handlebars {
        variables {
            publicPath = /assets
            siteName = My Site
        }
    }

..  _variables-global-service-container:

Via service container
---------------------

Variables can also be defined instance-wide through :file:`Services.yaml`.
These apply regardless of TypoScript configuration:

..  code-block:: yaml
    :caption: Configuration/Services.yaml

    handlebars:
      variables:
        publicPath: /assets
        apiEndpoint: https://api.example.com

..  note::

    When the same key is defined in both sources, the TypoScript value takes
    precedence.

..  _variables-per-rendering:

Per-rendering variables
=======================

Variables scoped to a single rendering are declared in the
:typoscript:`variables` property of a :typoscript:`HANDLEBARSTEMPLATE`
content object. Each entry is processed as a standard content object
against the current record's data:

..  code-block:: typoscript

    tt_content.header = HANDLEBARSTEMPLATE
    tt_content.header {
        templateName = Header

        variables {
            header = TEXT
            header.field = header

            subheader = TEXT
            subheader.field = subheader

            link = TEXT
            link.typolink.parameter.field = header_link
        }
    }

Entries with no sub-configuration are treated as **simple variables** and
passed to the template as-is, without invoking :php:`ContentObjectRenderer`:

..  code-block:: typoscript

    variables {
        # Content object — field value is rendered via cObjGetSingle
        header = TEXT
        header.field = header

        # Simple variables — values are passed through directly
        cssClass = my-element
        theme = dark
    }

Two variables are always injected automatically and cannot be overridden
(this reflects the same behavior as in :typoscript:`FLUIDTEMPLATE`):

:typoscript:`data`
    The full data array of the current content element record.

:typoscript:`current`
    The value of the current field (:php:`$cObj->currentValKey`).

..  warning::

    Declaring :typoscript:`data` or :typoscript:`current` in
    :typoscript:`variables` is not allowed and raises an exception.

..  seealso::

    :ref:`content-object` for the complete :typoscript:`HANDLEBARSTEMPLATE`
    property reference, including :typoscript:`settings` and
    :typoscript:`dataProcessing`.
