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

The :typoscript:`iterable` property does not name the source value directly.
Instead, it names the *key* under which the value is stored, and that key is
looked up across all four data sources, tried in the order listed:

+-----------------------------------+---------------------------------------------------+
| Data source identifier            | Contains                                          |
+===================================+===================================================+
| :php:`processorConfiguration`     | This processor's own config block                 |
+-----------------------------------+---------------------------------------------------+
| :php:`processedData`              | Accumulated output from previous processors       |
+-----------------------------------+---------------------------------------------------+
| :php:`contentObjectRenderer`      | Current record's field values                     |
+-----------------------------------+---------------------------------------------------+
| :php:`contentObjectConfiguration` | Top-level :typoscript:`HANDLEBARSTEMPLATE` config |
+-----------------------------------+---------------------------------------------------+

This is what allows :typoscript:`iterable` to refer to a value placed into
:typoscript:`processedData` by a preceding processor's :typoscript:`as`
option, or to a variable an Extbase controller assigned directly to the view.

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
                    iterable = news
                    as = newsItems
                }
            }
        }
    }

..  _data-processor-iterable-to-array-nested:

Processing individual items
============================

Each converted item is wrapped as :typoscript:`data` and run through a
nested :typoscript:`dataProcessing` chain, just like TYPO3's own
:typoscript:`database-query` processor does for its records. This only
happens when a nested chain is actually configured, so plain conversions are
left untouched:

..  code-block:: typoscript

    dataProcessing {
        10 = iterable-to-array
        10 {
            iterable = news
            as = newsItems

            dataProcessing {
                10 = object-access
                10 {
                    object = data
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
    Key under which the source value is looked up (see
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
