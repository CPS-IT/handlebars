..  include:: /Includes.rst.txt

..  _usage-data-sources:

============
Data sources
============

Data processors often need to work on data that was prepared earlier, for
example the files returned by TYPO3's :typoscript:`files` processor or an
object assigned by an Extbase controller. The data processors provided by this
extension can read such data from so-called *data sources*.

..  contents::
    :local:
    :depth: 1

..  _usage-data-sources-overview:

Available data sources
======================

+------------------------------------------+---------------------------------------------------+
| Data source identifier                   | Contains                                          |
+==========================================+===================================================+
| :typoscript:`processorConfiguration`     | This processor's own config block                 |
+------------------------------------------+---------------------------------------------------+
| :typoscript:`processedData`              | Output of previous processors and variables       |
+------------------------------------------+---------------------------------------------------+
| :typoscript:`contentObjectRenderer`      | Current record's field values                     |
+------------------------------------------+---------------------------------------------------+
| :typoscript:`contentObjectConfiguration` | :typoscript:`HANDLEBARSTEMPLATE` configuration,   |
|                                          | or the configuration forwarded by a parent        |
|                                          | processor                                         |
+------------------------------------------+---------------------------------------------------+

..  _usage-data-sources-payload:

Referencing data
================

Processors such as :ref:`data-processor-process-each` accept a
:typoscript:`dataSource` option that tells them which data to work on. Some
processors use a more specific option name for the same purpose, e.g.
:typoscript:`iterable` (:ref:`data-processor-iterable-to-array`),
:typoscript:`object` (:ref:`data-processor-object-access`) or
:typoscript:`file` (:ref:`data-processor-media`).

A reference consists of the data source identifier, a colon and the key
within that data source. Nested keys are separated by :typoscript:`/`:

..  code-block:: typoscript

    dataProcessing {
        10 = files
        10 {
            references.fieldName = image
            as = files
        }

        # All files
        20 = process-each
        20 {
            dataSource = processedData:files
            # ...
        }

        # The first file only
        30 = media
        30 {
            file = processedData:files/0
            as = image
        }
    }

Instead of referencing existing data, inline data can be configured with
the :typoscript:`data` option:

..  code-block:: typoscript

    20 = process-each
    20 {
        data {
            10 = foo
            20 = bar
        }
    }

If a reference cannot be resolved, a warning is logged and the processor
leaves the processed data unchanged.

..  seealso::

    :ref:`developer-corner-data-sources` for the complete resolution rules,
    e.g. how multiple references are merged.

..  _usage-data-sources-current:

Using the current value
=======================

Processors that run nested inside :ref:`data-processor-process-each` or
:ref:`data-processor-iterable-to-array` receive the item currently being
processed as the content object's *current value*. Set :typoscript:`current = 1`
as sub-property of the data source option to use it. This works for all
processors listed above, e.g. :typoscript:`dataSource.current`,
:typoscript:`object.current` or :typoscript:`iterable.current`:

..  code-block:: typoscript

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
        }
    }

Alternatively, the item can be referenced as
:typoscript:`contentObjectConfiguration:currentValue`.

..  _usage-data-sources-current-wrapped:

Wrapped current values
----------------------

:typoscript:`stdWrap` functions expect the current value to be a string.
Values which cannot be converted to strings, such as arrays or objects, would
therefore break TypoScript such as :typoscript:`current = 1` or
:typoscript:`if.isTrue.current = 1`. To prevent this, such values are wrapped
in a :php:`CPSIT\Typo3Handlebars\DataProcessing\DataSource\CurrentValue`
object before being provided as current value. Scalar values, :php:`null` and
stringable objects (implementing :php:`\Stringable`) are provided as-is.

For wrapped, non-stringable values, the following applies:

*   Within :typoscript:`stdWrap`, the current value resolves to an empty
    string (so e.g. :typoscript:`ifEmpty` applies).
*   The :typoscript:`current = 1` sub-property of data source options (e.g.
    :typoscript:`object.current`) resolves to the original, unwrapped value.
*   Custom code reading the current value via
    :php:`ContentObjectRenderer::getCurrentVal()` receives the wrapper and can
    access the original value via its :php:`value` property, see
    :ref:`developer-corner-data-source-aware-processor-current-value`.
