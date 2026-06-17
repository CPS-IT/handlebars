..  include:: /Includes.rst.txt

..  _installation:

============
Installation
============

..  _requirements:

Requirements
============

-   PHP 8.2 - 8.5
-   TYPO3 13.4 LTS - 14.3 LTS

..  _steps:

Installation
============

Require the extension via Composer (recommended):

..  code-block:: bash

    composer require cpsit/typo3-handlebars

Or download it from the
`TYPO3 extension repository <https://extensions.typo3.org/extension/handlebars>`__.

..  _site-sets:

Site sets
=========

The extension provides two :ref:`site sets <t3coreapi:site-sets>` that can be included in the site
configuration or any other site set.

*   :typoscript:`cpsit/handlebars` — **Handlebars base**

    -   Wires :typoscript:`plugin.tx_handlebars.view.templateRootPaths` and
        :typoscript:`plugin.tx_handlebars.view.partialRootPaths` from the site settings
        :typoscript:`handlebars.view.templateRootPath` and
        :typoscript:`handlebars.view.partialRootPath`.
    -   Include this set for every site that renders Handlebars templates.

*   :typoscript:`cpsit/handlebars-content-element` — **Handlebars content elements**

    -   Sets :typoscript:`lib.contentElement = HANDLEBARSTEMPLATE`, replacing the
        default Fluid base object used by EXT:fluid_styled_content.
    -   Include this set when all content elements should use Handlebars rendering by default.

..  code-block:: yaml
    :caption: config/sites/<my-site>/config.yaml

    dependencies:
      - cpsit/handlebars
      - cpsit/handlebars-content-element
