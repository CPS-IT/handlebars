..  include:: /Includes.rst.txt

..  _data-processor-iterable-to-array:

==================
iterable-to-array
==================

**Class:** :php:`CPSIT\Typo3Handlebars\DataProcessing\IterableToArrayProcessor`

Converts an iterable value — an Extbase :php:`QueryResultInterface` as
returned by a repository, an :php:`ObjectStorage`, a plain :php:`Iterator`,
or a :php:`Generator` — into a plain array. Templates and other data
processors generally expect array data, so this processor is the usual
bridge between a repository result and the rest of the
:typoscript:`dataProcessing` chain.

..  contents::
    :local:
    :depth: 1

..  _data-processor-iterable-to-array-data-sources:

Data sources
============

:typoscript:`iterable` is resolved exactly like :typoscript:`dataSource` on
:ref:`data-processor-process-each` — see :ref:`usage-data-sources-payload`
for the full syntax, including what happens when multiple references are
configured. It just uses a processor-specific option name instead of the
generic :typoscript:`dataSource`.

This is what allows :typoscript:`iterable` to refer to a value placed into
:typoscript:`processedData` by a preceding processor's :typoscript:`as`
option, or to a variable an Extbase controller assigned directly to the view
(Extbase-assigned view variables end up in :typoscript:`processedData` too,
under the assigned key).

..  _data-processor-iterable-to-array-usage:

Usage
=====

..  code-block:: php
    :caption: EXT:my_extension/Classes/Controller/NewsController.php

    final class NewsController extends HandlebarsController
    {
        public function listAction(): ResponseInterface
        {
            $this->view->assign('news', $this->newsRepository->findAll());

            return $this->htmlResponse($this->renderView());
        }
    }

..  code-block:: typoscript

    plugin.tx_myextension_news.handlebars {
        News::list {
            dataProcessing {
                10 = iterable-to-array
                10 {
                    iterable = processedData:news
                    as = newsItems
                }
            }
        }
    }

..  _data-processor-iterable-to-array-nested:

Processing individual items
============================

Each converted item is exposed to a nested :typoscript:`dataProcessing`
chain as :typoscript:`currentValue`, reachable as
:typoscript:`contentObjectConfiguration:currentValue` (see
:ref:`usage-data-sources`). This only happens when a nested chain is
actually configured, so plain conversions are left untouched:

..  code-block:: typoscript

    dataProcessing {
        10 = iterable-to-array
        10 {
            iterable = processedData:news
            as = newsItems

            dataProcessing {
                10 = object-access
                10 {
                    object = contentObjectConfiguration:currentValue
                    path = title
                    as = title
                }
            }
        }
    }

..  _data-processor-iterable-to-array-properties:

Properties
==========

:typoscript:`iterable`
    Data source reference(s) the value to convert is read from (see
    :ref:`data-processor-iterable-to-array-data-sources`). Required.

:typoscript:`as`
    Target key in the processed data array the resulting array is stored
    under. Default: :typoscript:`result`.

:typoscript:`preserveKeys`
    Boolean. When :typoscript:`1`, the original keys of the iterable (e.g.
    array keys or :php:`Generator` keys) are preserved. When :typoscript:`0`,
    the result is reindexed as a plain list. Default: :typoscript:`0`.

:typoscript:`dataProcessing`
    Nested data processors, run for each item of the converted array (see
    :ref:`data-processor-iterable-to-array-nested`).

If :typoscript:`iterable` is not configured, or the resolved value is not
iterable, a warning is logged and the processed data is returned unchanged.
