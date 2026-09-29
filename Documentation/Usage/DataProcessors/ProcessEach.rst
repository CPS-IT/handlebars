..  include:: /Includes.rst.txt

..  _data-processor-process-each:

============
process-each
============

**Class:** :php:`CPSIT\Typo3Handlebars\DataProcessing\ProcessEachProcessor`

Iterates over an array (or other iterable) and, for each item, evaluates a
:typoscript:`variables` configuration block and/or runs a nested
:typoscript:`dataProcessing` chain against that single item. This is the
usual way to enrich or reshape every entry of an array produced by an
earlier processor — for example, running :typoscript:`object-access`
against each file reference returned by TYPO3's core :typoscript:`files`
processor.

..  contents::
    :local:
    :depth: 1

..  _data-processor-process-each-source:

Resolving the items to iterate
===============================

The value to iterate over is resolved in the following order:

#.  :typoscript:`dataSource`, if configured (see
    :ref:`usage-data-sources-payload`).

#.  Otherwise, an inline :typoscript:`data` array configured directly on
    this processor.

#.  Otherwise, a :typoscript:`data` key already present in
    :php:`processedData`. This makes :typoscript:`process-each` usable as a
    nested processor inside TYPO3's core processors such as
    :typoscript:`database-query` or :typoscript:`menu` — which expose each
    record as :typoscript:`data` to their nested :typoscript:`dataProcessing`
    chain — without having to repeat :typoscript:`dataSource` explicitly.

If none of these yield an iterable value, the processed data is returned
unchanged.

..  _data-processor-process-each-usage:

Usage
=====

..  code-block:: typoscript

    tt_content.textpic = HANDLEBARSTEMPLATE
    tt_content.textpic {
        templateName = @textpic

        dataProcessing {
            10 = files
            10 {
                references.fieldName = image
                as = files
            }

            20 = process-each
            20 {
                dataSource = processedData:files
                as = processedFiles

                dataProcessing {
                    10 = object-access
                    10 {
                        object.current = 1
                        path = publicUrl
                        as = url
                    }

                    20 = object-access
                    20 {
                        object.current = 1
                        path = fileType
                        as = type
                    }
                }
            }
        }
    }

Each file reference resolved by the core :typoscript:`files` processor is
set as the content object's current value while the nested
:typoscript:`dataProcessing` chain runs, so :typoscript:`object-access` can
pull individual properties off it with :typoscript:`object.current = 1`
(see :ref:`usage-data-sources-current`). The per-item results are collected — keyed by
the original array keys — under :typoscript:`processedFiles`.

..  _data-processor-process-each-per-item:

Per-item processing
====================

Two mechanisms are available for each item, and can be combined:

:typoscript:`variables`
    A :typoscript:`variables` block, processed exactly like the top-level
    :typoscript:`variables` of :typoscript:`HANDLEBARSTEMPLATE`, but with the
    content object's *current value* set to the item. Use
    :typoscript:`current = 1` (instead of :typoscript:`field`) to reference
    the item itself:

    ..  code-block:: typoscript

        20 = process-each
        20 {
            dataSource = processedData:tags
            as = processedTags

            variables {
                label = TEXT
                label.current = 1
                label.case = upper
            }
        }

    :typoscript:`data` and :typoscript:`current` are reserved and cannot be
    used as variable names here.

:typoscript:`dataProcessing`
    A standard nested :typoscript:`dataProcessing` chain (see
    :ref:`data-processor-process-each-usage`), with the item set as current
    value. Its result is merged with — and overrides —
    whatever :typoscript:`variables` produced for the same item.

..  _data-processor-process-each-properties:

Properties
==========

:typoscript:`data`
    Inline data to iterate over, used if :typoscript:`dataSource` is not
    configured (see :ref:`data-processor-process-each-source`).

:typoscript:`dataSource`
    Data source(s) to read the iterable from (see
    :ref:`data-processor-process-each-source`).
    :typoscript:`dataSource.current` uses the content object's current value
    instead (see :ref:`usage-data-sources-current`).

:typoscript:`as`
    Target key in the processed data array the resulting (keyed) array is
    stored under. Default: :typoscript:`result`.

:typoscript:`variables`
    Per-item variables block (see
    :ref:`data-processor-process-each-per-item`).

:typoscript:`dataProcessing`
    Per-item nested data processors (see
    :ref:`data-processor-process-each-per-item`).
