..  include:: /Includes.rst.txt

..  _developer-corner-data-sources:

======================
Data source resolution
======================

This page describes in detail how data sources are resolved. It complements
the introduction in :ref:`usage-data-sources` and is relevant when combining
nested processors or implementing a
:ref:`DataSourceAwareProcessor <developer-corner-data-source-aware-processor>`.

..  contents::
    :local:
    :depth: 1

..  _developer-corner-data-sources-availability:

Availability
============

Not every data source is available in every context, and not every source
necessarily holds what its name suggests:

*   A processor invoked through TYPO3's :typoscript:`dataProcessing` chain
    always receives a :typoscript:`contentObjectConfiguration`. Once it is
    nested inside another processor's own :typoscript:`dataProcessing`, that
    value is no longer the top-level :typoscript:`HANDLEBARSTEMPLATE`
    configuration — it is whatever the parent processor forwards instead
    (e.g. :typoscript:`currentValue`).
*   The collection built for :typoscript:`HANDLEBARSTEMPLATE`'s own
    :typoscript:`preProcessing`/:typoscript:`postProcessing` hooks sets only
    :typoscript:`contentObjectRenderer` and :typoscript:`processorConfiguration`;
    :typoscript:`contentObjectConfiguration` and :typoscript:`processedData`
    are absent there.

..  _developer-corner-data-sources-priority:

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

..  _developer-corner-data-sources-nested-keys:

Nested keys
===========

A lookup key may use :typoscript:`/` to reach into a nested array within a
single data source, e.g. :typoscript:`some/nested/key`. Each segment is
looked up literally, one level at a time; the lookup fails (and any
configured default value is used) as soon as one segment does not exist.

A key is otherwise always matched literally, dots and all. This matters for
raw TypoScript arrays, which store sub-properties of a key under that same
key with a literal trailing dot appended (e.g. :typoscript:`dataProcessing.`
for the contents of a :typoscript:`dataProcessing { ... }` block) — such a
key is looked up as-is and is not itself treated as a path.

..  _developer-corner-data-sources-payload:

Payload resolution
==================

The :typoscript:`dataSource` option (or a processor-specific option name such
as :typoscript:`iterable`) is resolved as follows:

*   A **single reference** is resolved and used as-is, whatever its type — it
    is not coerced into an array.
*   **Multiple references**, configured as a TypoScript array with numeric
    keys, are resolved in ascending key order. If every reference resolves to
    an array, they are merged, with later references overriding earlier ones
    on key conflicts. If any reference does not resolve to an array, the last
    resolved reference wins outright — all earlier references, including any
    that were arrays, are discarded rather than partially merged.
*   :typoscript:`current = 1` bypasses the resolution entirely and uses the
    content object's current value
    (:php:`ContentObjectRenderer::getCurrentVal()`).

A warning is logged, and the payload cannot be resolved, if the option is
empty, references an unsupported data source identifier, a data source that
is missing in the current context, or a sub-path that does not exist within
a data source. An *unconfigured* option is not an error, though — in this case,
the inline :typoscript:`data` fallback applies.

The :typoscript:`data` fallback always uses the fixed key :typoscript:`data`
(:typoscript:`data.` for an inline array in :typoscript:`processorConfiguration`,
or a plain :typoscript:`data` key already present in :typoscript:`processedData`),
regardless of what the primary option is called for a given processor. See
each processor's documentation for further, processor-specific fallbacks.

In PHP, the same resolution is available via
:php:`DataSourceProvider::provide()`, see
:ref:`developer-corner-data-source-aware-processor-keyword`.
