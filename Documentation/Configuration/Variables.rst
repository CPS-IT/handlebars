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
on every page where the TypoScript is active. This is best suited for
**dynamic** variables that depend on the current request context, since the
underlying provider can only resolve them once a request is available (see
the tip below):

..  code-block:: typoscript

    plugin.tx_handlebars {
        variables {
            pageTitle = TEXT
            pageTitle.data = page:title

            campaign = TEXT
            campaign.field = campaign
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

..  tip::

    Prefer the service container for **static** variables. The underlying
    :php:`GlobalVariableProvider` is always cacheable, so its variables are
    resolved once and reused across renderings.

    :typoscript:`plugin.tx_handlebars.variables` is backed by
    :php:`TypoScriptVariableProvider`, which cannot resolve variables until a
    request with a :php:`ContentObjectRenderer` attached is available — this
    may not yet be the case during early bootstrapping. Because of this, it
    reports itself as non-cacheable until a request has been resolved, which
    disables caching of the merged variable set for that rendering. Reserve
    :typoscript:`plugin.tx_handlebars.variables` for **dynamic** variables
    that genuinely depend on the current request context, for example a
    :typoscript:`TEXT` content object reading a GET parameter or the current
    page record.

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
