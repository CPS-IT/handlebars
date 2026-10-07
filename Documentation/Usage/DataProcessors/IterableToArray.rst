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

:typoscript:`iterable` references the value to convert, e.g. the output of a
preceding processor or a variable assigned by an Extbase controller
(:typoscript:`processedData:news`). See :ref:`usage-data-sources-payload`
for the syntax.

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

Each converted item is set as the content object's current value while a
nested :typoscript:`dataProcessing` chain runs, so nested processors can
reference it with :typoscript:`current = 1` (see
:ref:`usage-data-sources-current`). Arrays and non-stringable objects are
wrapped before being set as current value (see
:ref:`usage-data-sources-current-wrapped`). This only happens when a nested
chain is actually configured, so plain conversions are left untouched:

..  code-block:: typoscript

    dataProcessing {
        10 = iterable-to-array
        10 {
            iterable = processedData:news
            as = newsItems

            dataProcessing {
                10 = object-access
                10 {
                    object.current = 1
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
