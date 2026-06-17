..  include:: /Includes.rst.txt

..  _migration:

=========
Migration
=========

This page lists required migration steps when upgrading to a new major version
of the extension.

..  _version-1.0.0:

Version 1.0.0
=============

Version 1.0.0 replaces the previous PHP-class rendering model (DataProcessor /
DataProvider / Presenter) with the :typoscript:`HANDLEBARSTEMPLATE` content
object. All rendering configuration moves to TypoScript; custom PHP classes are
no longer the entry point.

..  _migration-1-removed-classes:

Removed classes and interfaces
--------------------------------

The following classes and interfaces have been removed and have no replacement:

*   :php:`CPSIT\Typo3Handlebars\DataProcessing\DataProcessor` (interface)
*   :php:`CPSIT\Typo3Handlebars\DataProcessing\AbstractDataProcessor`
*   :php:`CPSIT\Typo3Handlebars\Data\DataProvider` (interface)
*   :php:`CPSIT\Typo3Handlebars\Data\Response\ProviderResponse` (interface)
*   :php:`CPSIT\Typo3Handlebars\Presenter\Presenter` (interface)
*   :php:`CPSIT\Typo3Handlebars\Presenter\AbstractPresenter`
*   :php:`CPSIT\Typo3Handlebars\DataProcessing\SimpleProcessor`

The :typoscript:`handlebars.processor` service tag has also been removed.

..  _migration-1-typoscript-entry-point:

TypoScript entry point
-----------------------

**Before:** Each content element was routed to a :php:`DataProcessor` class via
a :typoscript:`USER` content object:

..  code-block:: typoscript

    tt_content.header = USER
    tt_content.header.userFunc = Vendor\Extension\DataProcessing\HeaderProcessor->process

**After:** Use :typoscript:`HANDLEBARSTEMPLATE` directly:

..  code-block:: typoscript

    tt_content.header = HANDLEBARSTEMPLATE
    tt_content.header {
        templateName = Header

        variables {
            header = TEXT
            header.field = header

            subheader = TEXT
            subheader.field = subheader
        }
    }

..  _migration-1-data-preparation:

Data preparation (DataProvider → variables / dataProcessing)
-------------------------------------------------------------

**Before:** Data was prepared in a :php:`DataProvider` class and returned as a
:php:`ProviderResponse` object, which the :php:`Presenter` then passed to the
renderer.

**After:** Data is prepared entirely in TypoScript:

*   Simple field values: use :typoscript:`variables` with content objects
    such as :typoscript:`TEXT`, :typoscript:`FILES`, etc.
*   Database relations and menus: use standard TYPO3 :typoscript:`dataProcessing`
    processors (e.g., :typoscript:`database-query`, :typoscript:`menu`).
*   Per-record variable processing inside a loop: use :ref:`process-variables
    <data-processor-process-variables>`.

..  _migration-1-template-selection:

Template selection (Presenter → templateName)
----------------------------------------------

**Before:** The :php:`Presenter` called :php:`$this->renderer->render('path/to/template', $data)`.

**After:** The template is declared in TypoScript:

..  code-block:: typoscript

    tt_content.my_element {
        templateName = MyElement
    }

For conditional template selection, use stdWrap on :typoscript:`templateName`:

..  code-block:: typoscript

    tt_content.my_element {
        templateName = MyElement
        templateName.override.if {
            isTrue.field = tx_myext_variant
            value = special
        }
        templateName.override = MyElementSpecial
    }

..  _migration-1-helper-registration:

Helper registration
--------------------

**Before:** Helpers were registered via :file:`Services.yaml` tags:

..  code-block:: yaml
    :caption: Configuration/Services.yaml

    services:
      Vendor\Extension\Renderer\Helper\GreetHelper:
        tags:
          - name: handlebars.helper
            identifier: 'greet'
            method: 'greetById'

**After:** Use the :php:`#[AsHelper]` attribute directly on the class or method:

..  code-block:: php
    :caption: EXT:my_extension/Classes/Renderer/Helper/GreetHelper.php

    use CPSIT\Typo3Handlebars\Attribute\AsHelper;
    use CPSIT\Typo3Handlebars\Renderer\Helper\Helper;
    use DevTheorem\Handlebars\HelperOptions;

    #[AsHelper('greet')]
    final readonly class GreetHelper implements Helper
    {
        public function render(HelperOptions $options): string { /* ... */ }
    }

The :file:`Services.yaml` tag approach still works and can be used if you
cannot modify the helper class (e.g., a third-party class).

..  _migration-1-template-paths:

Template path configuration
----------------------------

Template path configuration via :file:`Services.yaml` and TypoScript remains
unchanged. In addition, paths can now also be set per-content-object directly
in :typoscript:`HANDLEBARSTEMPLATE`:

..  code-block:: typoscript

    tt_content.textmedia = HANDLEBARSTEMPLATE
    tt_content.textmedia {
        templateRootPaths.10 = EXT:my_extension/Resources/Private/Templates
        partialRootPaths.10 = EXT:my_extension/Resources/Private/Partials
    }

..  seealso::

    :ref:`template-paths` for the full configuration reference.
