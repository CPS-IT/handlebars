..  include:: /Includes.rst.txt

..  _quick-start:

===========
Quick start
===========

This page walks through a minimal working example: rendering the `header`
CType with a Handlebars template.

..  rst-class:: bignums-xxl

#.  Include site sets

    The extension ships two site sets. Include them in your site's
    configuration via the site module or :file:`config/sites/<site>/sets.yaml`.

    :typoscript:`cpsit/handlebars` (required)
        Wires :typoscript:`plugin.tx_handlebars.view` paths from the site
        settings :typoscript:`handlebars.view.templateRootPath` and
        :typoscript:`handlebars.view.partialRootPath`. Required for every site
        that renders Handlebars templates.

    :typoscript:`cpsit/handlebars-content-element` (optional)
        Sets :typoscript:`lib.contentElement = HANDLEBARSTEMPLATE`, replacing
        the default Fluid base object. Include this set when all content
        elements should use Handlebars rendering by default.

#.  Configure template paths

    Declare where your :file:`.hbs` files are located. The simplest option is
    TypoScript:

    ..  code-block:: typoscript

        plugin.tx_handlebars {
            view {
                templateRootPaths {
                    10 = EXT:my_sitepackage/Resources/Private/Templates/Handlebars
                }
                partialRootPaths {
                    10 = EXT:my_sitepackage/Resources/Private/Partials/Handlebars
                }
            }
        }

    ..  seealso::

        :ref:`template-paths` — all configuration methods and their priority order.

#.  Create a Handlebars template

    Create the template file at the path declared above. The filename without
    the :file:`.hbs` extension is used as the :typoscript:`templateName`:

    ..  code-block:: handlebars
        :caption: EXT:my_sitepackage/Resources/Private/Templates/Handlebars/Header.hbs

        <div class="ce-header">
            {{#if header}}
                <h1 class="ce-header__title">{{header}}</h1>
            {{/if}}
            {{#if subheader}}
                <p class="ce-header__subtitle">{{subheader}}</p>
            {{/if}}
        </div>

#.  Configure the content element

    Point the `header` CType at the template using a :typoscript:`HANDLEBARSTEMPLATE` content object:

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

    Each entry in :typoscript:`variables` is processed as a TYPO3 content
    object against the current content element record. The resulting values
    are passed to the template alongside the automatically injected
    :typoscript:`data` and :typoscript:`current` variables.

#.  Flush caches

    After editing TypoScript, flush the TYPO3 page cache. After editing
    :file:`Services.yaml`, flush and rebuild the service container as well.

..  _quick-start-next-steps:

Next steps
==========

*   :ref:`content-object` — complete reference for all
    :typoscript:`HANDLEBARSTEMPLATE` properties
*   :ref:`data-processors` — enrich templates with database queries, menus,
    and custom variable processing
*   :ref:`custom-helpers` — expose custom PHP logic to templates
