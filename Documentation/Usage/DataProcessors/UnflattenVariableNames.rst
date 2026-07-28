..  include:: /Includes.rst.txt

..  _data-processor-unflatten-variable-names:

========================
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
