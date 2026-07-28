..  include:: /Includes.rst.txt

..  _data-processors:

===============
Data processors
===============

The extension provides four data processors that integrate with the standard
TypoScript :typoscript:`dataProcessing` chain inside :typoscript:`HANDLEBARSTEMPLATE`
content objects.

..  contents::
    :local:
    :depth: 1

----

..  _data-processor-object-access:

object-access
=============

**Class:** :php:`CPSIT\Typo3Handlebars\DataProcessing\ObjecAccessProcessor`

Resolves a property (or nested property path) from an object and stores the
resulting value in the processed data. This is useful for pulling individual
properties out of an Extbase domain object (or any other object) that was
placed into the data processing chain by a preceding processor or controller,
without having to expose the whole object to the template.

..  _data-processor-object-access-data-sources:

Data sources
------------

The :typoscript:`object` property does not name a source object directly.
Instead, it names the *key* under which the object is stored, and that key is
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

This is what allows :typoscript:`object` to refer to a value placed into
:typoscript:`processedData` by a preceding processor's :typoscript:`as`
option, or to a variable an Extbase controller assigned directly to the
view.

..  _data-processor-object-access-usage:

Usage
-----

:typoscript:`dataProcessing` also runs for Extbase plugins whose controller
extends :ref:`extbase-plugin-handlebars-controller`, since :php:`HandlebarsView`
renders through the same :typoscript:`HANDLEBARSTEMPLATE` content object under
the hood. An action that assigns a domain object to the view makes that object
available to :typoscript:`object-access` under the assigned key:

..  code-block:: php
    :caption: EXT:my_extension/Classes/Controller/BlogController.php

    final class BlogController extends HandlebarsController
    {
        public function showAction(Post $post): ResponseInterface
        {
            $this->view->assign('post', $post);

            return $this->htmlResponse($this->renderView());
        }
    }

..  code-block:: typoscript

    plugin.tx_myextension_blog.handlebars {
        Blog::show {
            dataProcessing {
                # Pull the related category's title off the assigned "post" object
                10 = object-access
                10 {
                    object = post
                    path = category.title
                    as = categoryTitle
                }
            }
        }
    }

..  _data-processor-object-access-properties:

Properties
----------

:typoscript:`object`
    Key under which the source object is looked up (see
    :ref:`data-processor-object-access-data-sources`). Required.

:typoscript:`path`
    Property path passed to :php:`TYPO3\CMS\Extbase\Reflection\ObjectAccess::getProperty()`.
    Supports plain property names as well as dot-separated nested paths
    (e.g. :typoscript:`category.title`). Required.

:typoscript:`as`
    Target key in the processed data array the resolved value is stored
    under. Default: :typoscript:`result`.

If :typoscript:`object` is not configured, or the resolved value is not an
object, or :typoscript:`path` is missing, or :typoscript:`path` is not a
gettable property on the resolved object, a warning is logged and the
processed data is returned unchanged.

When the resolved value is itself an array, it is run through the standard
TYPO3 :typoscript:`dataProcessing` chain configured on this processor, just
like TYPO3's own :typoscript:`menu` or :typoscript:`database-query`
processors do for their items.

----

..  _data-processor-process-variables:

process-variables
=================

**Class:** :php:`CPSIT\Typo3Handlebars\DataProcessing\ProcessVariablesProcessor`

Processes a :typoscript:`variables` configuration block — exactly like the
top-level :typoscript:`variables` of :typoscript:`HANDLEBARSTEMPLATE` — within
a data processor chain. This is most useful when combined with other processors
such as :typoscript:`database-query`, allowing per-record variable processing.

..  _data-processor-process-variables-data-sources:

Data sources
------------

When resolving configuration values, the processor draws from four data
sources, tried in the order listed:

+-----------------------------------+---------------------------------------------------+
| Data source identifier            | Contains                                          |
+===================================+===================================================+
| :php:`contentObjectRenderer`      | Current record's field values                     |
+-----------------------------------+---------------------------------------------------+
| :php:`contentObjectConfiguration` | Top-level :typoscript:`HANDLEBARSTEMPLATE` config |
+-----------------------------------+---------------------------------------------------+
| :php:`processedData`              | Accumulated output from previous processors       |
+-----------------------------------+---------------------------------------------------+
| :php:`processorConfiguration`     | This processor's own config block                 |
+-----------------------------------+---------------------------------------------------+

This is why options like :typoscript:`table` and :typoscript:`as` can be set
by an outer processor and automatically picked up by a nested
:typoscript:`process-variables` without being repeated explicitly.
The :typoscript:`preProcessing` and :typoscript:`postProcessing` hooks receive
the same collection, so they have access to all four sources as well.

..  _data-processor-process-variables-standalone:

Standalone usage
----------------

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
---------------------------------

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
----------

:typoscript:`variables`
    Variables to process. Same syntax as the top-level
    :typoscript:`variables` in :typoscript:`HANDLEBARSTEMPLATE`.

:typoscript:`table`
    Database table of the record to use as the data source for field
    lookups. Defaults to the current content element table.

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

----

..  _data-processor-resolve-markers:

resolve-markers
===============

**Class:** :php:`CPSIT\Typo3Handlebars\DataProcessing\ResolveMarkersProcessor`

Replaces marker-style keys (e.g., :typoscript:`###NAV_ITEMS###`) in the
processed data with the values stored under those keys. The typical pattern is
to use a marker as a named placeholder early in the chain — either as the
:typoscript:`as` target of a preceding processor or directly in a
:typoscript:`variables` entry — and then resolve all markers to clean variable
names in a final step. This keeps intermediate processors decoupled from the
variable names the template expects.

..  code-block:: typoscript

    tt_content.my_element = HANDLEBARSTEMPLATE
    tt_content.my_element {
        templateName = MyElement

        dataProcessing {
            # Declare the expected output slots early using markers — these
            # names will become the final template variables
            10 = menu
            10 {
                as = ###mainNavigation###
                levels = 2
            }

            20 = menu
            20 {
                as = ###footerLinks###
                special = directory
                special.value = 42
            }

            # Resolve all markers to clean variable names in one final step
            90 = resolve-markers
            90.removeNonMatchingMarkers = 1
        }
    }

After processing, the template receives :typoscript:`mainNavigation` and
:typoscript:`footerLinks` as clean variable names. The markers at the top of
the chain serve as upfront documentation of what the template expects, while
the processors that follow fill those slots independently.

..  _data-processor-resolve-markers-properties:

Properties
----------

:typoscript:`pattern`
    Regular expression used to identify marker keys. The first capture
    group becomes the resolved variable name.
    Default: :typoscript:`###(.*?)###`

:typoscript:`removeNonMatchingMarkers`
    Boolean. When :typoscript:`1`, variable keys that still match the
    marker pattern after resolution (i.e., no value was found for them)
    are removed from the processed data. Default: :typoscript:`0`.

----

..  _data-processor-unflatten-variable-names:

unflatten-variable-names
========================

**Class:** :php:`CPSIT\Typo3Handlebars\DataProcessing\UnflattenVariableNamesProcessor`

Converts dot-separated flat variable names into nested arrays. This is useful
when other processors set their :typoscript:`as` key to a dotted path,
representing the intended position in a nested data structure.

..  code-block:: typoscript

    dataProcessing {
        10 = menu
        10 {
            as = page.nav.mainMenu
        }

        20 = menu
        20 {
            as = page.nav.footerLinks
            special = directory
            special.value = 42
        }

        90 = unflatten-variable-names
    }

After processing, the template receives a nested :typoscript:`page` object:

..  code-block:: handlebars

    {{#each page.nav.mainMenu}}
        <a href="{{link}}">{{title}}</a>
    {{/each}}

The processor has no configuration properties and operates on the entire
processed data array.
