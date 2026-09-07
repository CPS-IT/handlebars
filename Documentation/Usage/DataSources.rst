..  include:: /Includes.rst.txt

..  _usage-data-sources:

============
Data sources
============

Several data processors resolve part of their configuration, or their whole
input payload, from one or more *data sources* made available during
TypoScript :typoscript:`dataProcessing`. This page describes the concept
once; see :ref:`data-processor-iterable-to-array`,
:ref:`data-processor-object-access`, :ref:`data-processor-process-each` and
:ref:`data-processor-process-variables` for how each processor applies it.

..  contents::
    :local:
    :depth: 1

..  _usage-data-sources-overview:

Overview
========

+------------------------------------------+---------------------------------------------------+
| Data source identifier                   | Contains                                          |
+==========================================+===================================================+
| :typoscript:`processorConfiguration`     | This processor's own config block                 |
+------------------------------------------+---------------------------------------------------+
| :typoscript:`processedData`              | Accumulated output from previous processors       |
+------------------------------------------+---------------------------------------------------+
| :typoscript:`contentObjectRenderer`      | Current record's field values                     |
+------------------------------------------+---------------------------------------------------+
| :typoscript:`contentObjectConfiguration` | Top-level :typoscript:`HANDLEBARSTEMPLATE` config |
+------------------------------------------+---------------------------------------------------+

Not every source necessarily holds what its name suggests. A processor
invoked through TYPO3's :typoscript:`dataProcessing` chain always receives a
:typoscript:`contentObjectConfiguration`, but once it is nested inside
another processor's own :typoscript:`dataProcessing`, that value is no
longer the top-level :typoscript:`HANDLEBARSTEMPLATE` configuration — it is
whatever the parent processor forwards instead (its own config block, for
example). The collection built for :typoscript:`HANDLEBARSTEMPLATE`'s own
:typoscript:`preProcessing`/:typoscript:`postProcessing` hooks, which run
before :typoscript:`dataProcessing` itself, sets only
:typoscript:`contentObjectRenderer` and :typoscript:`processorConfiguration`;
:typoscript:`contentObjectConfiguration` and :typoscript:`processedData` are
genuinely absent there.

..  _usage-data-sources-priority:

Priority order
==============

When a lookup is not restricted to a specific source, all sources that are
actually available are searched in priority order, highest first:

#.  :typoscript:`processorConfiguration`
#.  :typoscript:`processedData`
#.  :typoscript:`contentObjectRenderer`
#.  :typoscript:`contentObjectConfiguration`

The first source that has the requested key wins; sources are never merged
for this kind of lookup. This is why an option like :typoscript:`table`, set
by an outer processor, can be picked up by a nested processor without being
repeated explicitly — as long as the nested processor actually queries that
source (some processors restrict a given option to a specific source, or a
specific subset, rather than searching all four).

..  _usage-data-sources-payload:

Resolving a data payload (`dataSource` / `data`)
================================================

:ref:`data-processor-process-each` and :ref:`data-processor-process-variables`
accept a :typoscript:`dataSource` (or inline :typoscript:`data`) option to
pick the actual payload they operate on, independently of whatever their own
configuration otherwise resolves from the four sources above.
:ref:`data-processor-iterable-to-array` and :ref:`data-processor-object-access`
use the exact same mechanism under a processor-specific option name —
:typoscript:`iterable` and :typoscript:`object` respectively — instead of the
generic :typoscript:`dataSource`.

:typoscript:`dataSource` (or the processor-specific option name)
    One or more data source references, each optionally scoped to a
    sub-path with a colon (e.g. :typoscript:`processedData:files`). A single
    reference is resolved and used as-is, whatever its type — it is not
    coerced into an array.

    Multiple references, configured as a TypoScript array with numeric
    keys, are resolved in ascending key order:

    *   If **every** reference resolves to an array, they are merged, with
        later references overriding earlier ones on key conflicts.
    *   If **any** reference does not resolve to an array, the last
        resolved reference wins outright — all earlier references,
        including any that were arrays, are discarded rather than
        partially merged.

    A warning is logged, and the payload cannot be resolved, if the option
    is empty, references an unsupported data source identifier, a data
    source that is missing in the current context, or a sub-path that does
    not exist within a data source. Note that an *unconfigured* option
    (rather than one configured with an invalid value) is not itself an
    error — see the fallback below and each processor's own documentation
    for what happens next.

:typoscript:`data`
    Inline data, used as a fallback if the option above is not configured
    at all. This fallback always uses the fixed key :typoscript:`data`
    (:typoscript:`data.` for an inline array in :typoscript:`processorConfiguration`,
    or a plain :typoscript:`data` key already present in
    :typoscript:`processedData`), regardless of what the primary option is
    called for a given processor.

See each processor's own documentation for further, processor-specific
fallbacks once neither option yields a value.
