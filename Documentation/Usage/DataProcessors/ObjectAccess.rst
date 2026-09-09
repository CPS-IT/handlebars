..  include:: /Includes.rst.txt

..  _data-processor-object-access:

=============
object-access
=============

**Class:** :php:`CPSIT\Typo3Handlebars\DataProcessing\ObjecAccessProcessor`

Resolves a property (or nested property path) from an object and stores the
resulting value in the processed data. This is useful for pulling individual
properties out of an Extbase domain object (or any other object) that was
placed into the data processing chain by a preceding processor or controller,
without having to expose the whole object to the template.

..  contents::
    :local:
    :depth: 1

..  _data-processor-object-access-data-sources:

Data sources
============

:typoscript:`object` is resolved exactly like :typoscript:`dataSource` on
:ref:`data-processor-process-each` — see :ref:`usage-data-sources-payload`
for the full syntax, including what happens when multiple references are
configured. It just uses a processor-specific option name instead of the
generic :typoscript:`dataSource`. :typoscript:`object.current` uses the
content object's current value instead (see
:ref:`usage-data-sources-current`).

This is what allows :typoscript:`object` to refer to a value placed into
:typoscript:`processedData` by a preceding processor's :typoscript:`as`
option, or to a variable an Extbase controller assigned directly to the
view (Extbase-assigned view variables end up in :typoscript:`processedData`
too, under the assigned key).

..  _data-processor-object-access-usage:

Usage
=====

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
                    object = processedData:post
                    path = category.title
                    as = categoryTitle
                }
            }
        }
    }

..  _data-processor-object-access-properties:

Properties
==========

:typoscript:`object`
    Data source reference(s) the source object is read from (see
    :ref:`data-processor-object-access-data-sources`). Required.

:typoscript:`path`
    Property path passed to :php:`TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider::getByPath()`.
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
