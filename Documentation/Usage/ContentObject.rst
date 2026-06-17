..  include:: /Includes.rst.txt

..  _content-object:

===================================
`HANDLEBARSTEMPLATE` content object
===================================

:typoscript:`HANDLEBARSTEMPLATE` is a custom content object type provided by
this extension. It compiles and renders a Handlebars template, resolving
template paths, processing variables, and registering assets — all from
TypoScript configuration.

..  code-block:: typoscript

    tt_content.header = HANDLEBARSTEMPLATE
    tt_content.header {
        templateName = Header
        templateRootPaths.20 = EXT:my_extension/Resources/Private/Templates
    }

..  contents:: Properties
    :local:
    :depth: 1

----

templateName
============

:aspect:`Type`
    string / stdWrap

:aspect:`Description`
    Name of the template to render. The value is resolved as a filename
    (without the :file:`.hbs` extension) relative to the configured template
    root paths. Exactly one of :typoscript:`templateName`, :typoscript:`template`,
    or :typoscript:`file` must be set.

:aspect:`Example`
    ..  code-block:: typoscript

        templateName = Header

        # With stdWrap
        templateName.field = tx_myext_template_name

----

template
========

:aspect:`Type`
    string / stdWrap

:aspect:`Description`
    Inline Handlebars source used directly as the template. Useful for short
    or dynamically constructed templates. Cannot be used together with
    :typoscript:`templateName` or :typoscript:`file`.

:aspect:`Example`
    ..  code-block:: typoscript

        template = <h1>{{header}}</h1>

----

file
====

:aspect:`Type`
    string / stdWrap

:aspect:`Description`
    Absolute or :typoscript:`EXT:`-relative path to a Handlebars template
    file. Cannot be used together with :typoscript:`templateName` or
    :typoscript:`template`.

:aspect:`Example`
    ..  code-block:: typoscript

        file = EXT:my_extension/Resources/Private/Templates/Special.hbs

----

templateRootPaths
=================

:aspect:`Type`
    array (numeric keys)

:aspect:`Description`
    Template root paths for this content object. These are added to the
    content object path provider with the highest priority (100), overriding
    any TypoScript or service container paths for this rendering. Higher
    numeric keys take precedence over lower ones.

:aspect:`Example`
    ..  code-block:: typoscript

        templateRootPaths {
            10 = EXT:my_extension/Resources/Private/Templates
            20 = EXT:my_other_extension/Resources/Private/Templates
        }

..  note::

    The shorthand :typoscript:`templateRootPath` (singular) sets a single
    path at key ``0``.

----

partialRootPaths
================

:aspect:`Type`
    array (numeric keys)

:aspect:`Description`
    Partial root paths for this content object. Same priority and override
    rules as :typoscript:`templateRootPaths`.

:aspect:`Example`
    ..  code-block:: typoscript

        partialRootPaths {
            10 = EXT:my_extension/Resources/Private/Partials
        }

..  note::

    The shorthand :typoscript:`partialRootPath` (singular) sets a single
    path at key ``0``.

----

variables
=========

:aspect:`Type`
    array

:aspect:`Description`
    Variables passed to the template. Each entry is processed as a content
    object against the current content element's data record. Simple
    string values are passed through as-is; entries with a sub-array are
    rendered via :php:`ContentObjectRenderer::cObjGetSingle()`.

    Two variable names are reserved and always available automatically:

    *   :typoscript:`data` — the full content element data array
    *   :typoscript:`current` — the current field value

:aspect:`Example`
    ..  code-block:: typoscript

        variables {
            header = TEXT
            header.field = header

            bodytext = TEXT
            bodytext.field = bodytext
            bodytext.parseFunc < lib.parseFunc_RTE

            image = FILES
            image.references.fieldName = image
        }

----

settings
========

:aspect:`Type`
    array

:aspect:`Description`
    Arbitrary key-value pairs passed to the template as the :typoscript:`settings`
    variable. Unlike :typoscript:`variables`, entries are not processed as
    content objects — values are used as plain strings.

:aspect:`Example`
    ..  code-block:: typoscript

        settings {
            showDate = 1
            dateFormat = d.m.Y
        }

    In the template:

    ..  code-block:: handlebars

        {{#if settings.showDate}}
            <time>{{formatDate date settings.dateFormat}}</time>
        {{/if}}

----

dataProcessing
==============

:aspect:`Type`
    array

:aspect:`Description`
    Standard data processors, executed after :typoscript:`variables` are resolved.
    Processors receive and return the :php:`$processedData` array. Any key added
    by a processor is available as a template variable.

    The extension provides three additional processors:
    :typoscript:`process-variables`, :typoscript:`resolve-markers`, and
    :typoscript:`unflatten-variable-names`.

:aspect:`Example`
    ..  code-block:: typoscript

        dataProcessing {
            10 = database-query
            10 {
                table = tx_myext_domain_model_item
                as = items
            }

            20 = process-variables
            20 {
                as = items
                merge = 1
                variables {
                    label = TEXT
                    label.field = title
                }
            }
        }

..  seealso::

    :ref:`data-processors` for documentation of the extension-specific
    processors.

----

preProcessing
=============

:aspect:`Type`
    array

:aspect:`Description`
    Data source aware processors executed before :typoscript:`variables`
    are processed. These can read from multiple data sources (content element
    record, processed data, processor configuration) and modify the variable
    set before content object rendering begins.

----

postProcessing
==============

:aspect:`Type`
    array

:aspect:`Description`
    Data source aware processors executed after :typoscript:`variables` have
    been resolved and data processors have run, but before the template is
    rendered.

----

assets
======

:aspect:`Type`
    array

:aspect:`Description`
    Registers JavaScript and CSS assets via TYPO3's AssetCollector API. Supports
    four sub-keys: :typoscript:`javaScript`, :typoscript:`inlineJavaScript`,
    :typoscript:`css`, :typoscript:`inlineCss`.

:aspect:`Example`
    ..  code-block:: typoscript

        assets {
            javaScript {
                my-ext-app {
                    source = EXT:my_extension/Resources/Public/JavaScript/app.js
                    attributes.defer = 1
                    options.useNonce = 1
                }
            }
            css {
                my-ext-styles {
                    source = EXT:my_extension/Resources/Public/Css/styles.css
                }
            }
        }

..  seealso::

    :ref:`asset-management` for the complete assets configuration reference.

----

headerAssets
============

:aspect:`Type`
    content object

:aspect:`Description`
    Adds arbitrary markup to the page :html:`<head>`. The value is evaluated
    as a content object and the result is passed to
    :php:`PageRenderer::addHeaderData()`.

:aspect:`Example`
    ..  code-block:: typoscript

        headerAssets = TEXT
        headerAssets.value = <link rel="stylesheet" href="/assets/styles.css">

----

footerAssets
============

:aspect:`Type`
    content object

:aspect:`Description`
    Adds arbitrary markup before the closing :html:`</body>` tag. The value
    is evaluated as a content object and the result is passed to
    :php:`PageRenderer::addFooterData()`.

:aspect:`Example`
    ..  code-block:: typoscript

        footerAssets = TEXT
        footerAssets.value = <script src="/assets/app.js"></script>

----

stdWrap
=======

:aspect:`Type`
    stdWrap

:aspect:`Description`
    Standard TYPO3 stdWrap processing applied to the final rendered output.

:aspect:`Example`
    ..  code-block:: typoscript

        stdWrap.wrap = <div class="handlebars-content">|</div>
