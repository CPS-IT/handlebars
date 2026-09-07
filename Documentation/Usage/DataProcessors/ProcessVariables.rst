..  include:: /Includes.rst.txt

..  _data-processor-process-variables:

=================
process-variables
=================

**Class:** :php:`CPSIT\Typo3Handlebars\DataProcessing\ProcessVariablesProcessor`

Processes a :typoscript:`variables` configuration block — exactly like the
top-level :typoscript:`variables` of :typoscript:`HANDLEBARSTEMPLATE` — within
a data processor chain. This is most useful when combined with other processors
such as :typoscript:`database-query`, allowing per-record variable processing.

..  contents::
    :local:
    :depth: 1

..  _data-processor-process-variables-data-sources:

Data sources
============

:typoscript:`as`, :typoscript:`merge` and :typoscript:`variables` are read
from this processor's own configuration block
(:typoscript:`processorConfiguration`) only. :typoscript:`table` is also read
from there, but falls back to a same-named key already present in
:php:`processedData` — this is why :typoscript:`table`, once set by an outer
processor (e.g. :typoscript:`database-query`), can be picked up by a nested
:typoscript:`process-variables` without being repeated explicitly. See
:ref:`usage-data-sources` for what these two sources contain.

The :typoscript:`preProcessing` and :typoscript:`postProcessing` hooks
receive the full :php:`DataSourceCollection`, so custom
:php:`DataSourceAwareProcessor` implementations have access to all four
sources (see :ref:`developer-corner-data-source-aware-processor`).

..  _data-processor-process-variables-payload:

Choosing which record to process
=================================

By default, the :typoscript:`field` option of a :typoscript:`variables`
entry resolves against the current content element's own record. Configure
:typoscript:`dataSource` (or an inline :typoscript:`data` array) to process
a different record or array instead — see :ref:`usage-data-sources-payload`
for how it is resolved. If the resolved payload is not an array, it is
ignored and the current record's own field values are used instead.

..  _data-processor-process-variables-standalone:

Standalone usage
================

..  code-block:: typoscript

    tt_content.my_element = HANDLEBARSTEMPLATE
    tt_content.my_element {
        templateName = MyElement

        dataProcessing {
            10 = process-variables
            10 {
                variables {
                    header = TEXT
                    header.field = header

                    teaser = TEXT
                    teaser.field = bodytext
                    teaser.parseFunc < lib.parseFunc_RTE
                }
            }
        }
    }

..  _data-processor-process-variables-nested:

Nested inside another processor
================================

..  code-block:: typoscript

    dataProcessing {
        10 = database-query
        10 {
            table = tx_myext_domain_model_item
            as = items

            dataProcessing {
                10 = process-variables
                10 {
                    table = tx_myext_domain_model_item
                    as = item
                    variables {
                        title = TEXT
                        title.field = title

                        body = TEXT
                        body.field = bodytext
                        body.parseFunc < lib.parseFunc_RTE
                    }
                }
            }
        }
    }

..  _data-processor-process-variables-properties:

Properties
==========

:typoscript:`variables`
    Variables to process. Same syntax as the top-level
    :typoscript:`variables` in :typoscript:`HANDLEBARSTEMPLATE`.

:typoscript:`table`
    Database table of the record to use as the data source for field
    lookups. Defaults to the current content element table.

:typoscript:`data`
    Inline data used as the field-lookup source for :typoscript:`variables`,
    if :typoscript:`dataSource` is not configured (see
    :ref:`data-processor-process-variables-payload`).

:typoscript:`dataSource`
    Data source(s) to use as the field-lookup source for
    :typoscript:`variables`, instead of the current record (see
    :ref:`data-processor-process-variables-payload`).

:typoscript:`as`
    Target key in the processed data array. When set, the processed
    variables are stored under this key. When omitted, the processed
    variables replace (or merge into) the root of the processed data.

:typoscript:`merge`
    Boolean. When :typoscript:`1` and :typoscript:`as` is omitted,
    the processed variables are merged into the existing processed data
    rather than replacing it. When :typoscript:`as` is set and the key
    already holds an array, the processed variables are merged into that
    array. Default: :typoscript:`0`.

:typoscript:`if`
    Standard TypoScript :typoscript:`if` condition. When the condition
    evaluates to false, the processor is skipped and the processed data
    is returned unchanged.

:typoscript:`preProcessing`
    Data source aware processors run before :typoscript:`variables` are
    processed.

:typoscript:`postProcessing`
    Data source aware processors run after :typoscript:`variables` are
    processed.
