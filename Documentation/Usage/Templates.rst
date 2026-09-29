..  include:: /Includes.rst.txt

..  _templates:

=================
Writing templates
=================

Templates are plain :file:`.hbs` files using the standard
`Handlebars syntax <https://handlebarsjs.com/guide/>`__: expressions, block
helpers such as :handlebars:`{{#if}}` and :handlebars:`{{#each}}`, partials
and comments all work as documented there. This page covers what the
extension adds on top: how template names are resolved, how to build layouts,
and which helpers are available out of the box.

..  contents::
    :local:
    :depth: 1

..  _templates-names:

Referencing templates and partials
==================================

Templates and partials are referenced by name, without the :file:`.hbs` file
extension. They are looked up in the configured
:ref:`template and partial root paths <template-paths>`. Two addressing styles
are supported.

..  _templates-names-relative:

Directory-relative names
------------------------

A name without prefix is resolved relative to the root paths, just like in
Fluid. :typoscript:`templateName = Blog/List` resolves to
:file:`Blog/List.hbs` in one of the template root paths, and
:handlebars:`{{> Components/Card}}` to :file:`Components/Card.hbs` in one of
the partial root paths.

..  _templates-names-flat:
..  _template-paths-flat-names:

Flat names
----------

A name prefixed with :file:`@` is looked up by its bare filename, regardless
of the subdirectory the file lives in:

..  code-block:: typoscript

    tt_content.tx_myext_teaser = HANDLEBARSTEMPLATE
    tt_content.tx_myext_teaser {
        # Finds e.g. Components/Molecules/teaser.hbs
        templateName = @teaser
    }

..  code-block:: handlebars

    {{> @card}}

If the same filename exists in multiple root paths, the root path with the
higher priority wins. This follows the
`Fractal <https://fractal.build/guide/core-concepts/naming.html>`__ naming
convention, so a Fractal component library can be used as template source
without changes.

Appending :file:`--<variant>` selects a named variant. If no dedicated file
exists for the variant, the base name is used instead:

..  code-block:: handlebars

    {{> @card--highlighted}}   {{!-- falls back to @card if not found --}}

..  _templates-partials:

Partials
========

Partials are included with the standard :handlebars:`{{> name}}` syntax.
Without further arguments, the partial receives the current context. Pass
a different context as positional argument and/or single values as hash
arguments:

..  code-block:: handlebars

    {{> @card}}
    {{> @card item}}
    {{> @card title=item.title image=item.image}}

The :handlebars:`render` helper is an alternative that allows passing and
merging contexts explicitly, see :ref:`templates-helpers-render`.

..  _templates-layouts:

Layouts
=======

Layouts are built with the :handlebars:`extend`, :handlebars:`block` and
:handlebars:`content` helpers, modelled after
`handlebars-layouts <https://github.com/shannonmoeller/handlebars-layouts>`__.
A layout is an ordinary partial that declares named slots with
:handlebars:`{{#block}}`. A block may contain default content that is used
if no template fills the slot:

..  code-block:: handlebars
    :caption: EXT:my_sitepackage/Resources/Private/Partials/Handlebars/default.hbs

    <main>
        {{#block "main"}}{{/block}}
    </main>
    <footer>
        {{#block "footer"}}
            <p>&copy; My Site</p>
        {{/block}}
    </footer>

A template wraps its markup in :handlebars:`{{#extend}}` and fills slots with
:handlebars:`{{#content}}`:

..  code-block:: handlebars
    :caption: EXT:my_sitepackage/Resources/Private/Templates/Handlebars/my-element.hbs

    {{#extend "default"}}
        {{#content "main"}}
            <h1>{{header}}</h1>
        {{/content}}
    {{/extend}}

By default, :handlebars:`{{#content}}` replaces the block's default content.
Pass :handlebars:`mode="append"` or :handlebars:`mode="prepend"` to add to it
instead.

..  note::

    Layouts are resolved as partials, so layout files must be placed in one of
    the configured partial root paths.

..  _templates-helpers:

Built-in helpers
================

Next to the helpers built into Handlebars itself (:handlebars:`if`,
:handlebars:`unless`, :handlebars:`each`, :handlebars:`with`,
:handlebars:`lookup`, :handlebars:`log`), the extension registers the
following helpers. To add your own, see :ref:`custom-helpers`.

..  _templates-helpers-layout:

extend, block, content
----------------------

Build layouts, see :ref:`templates-layouts`. :handlebars:`{{#extend}}`
accepts an optional context as second argument and hash arguments, which are
merged into the context passed to the layout.

..  _templates-helpers-render:

render
------

Renders a partial. The first argument is the partial name, the optional second
argument a custom context:

..  code-block:: handlebars

    {{render "@card"}}
    {{render "@card" item}}
    {{render "@card" item merge=true}}

Without custom context, the partial receives the root variable named like the
partial itself (e.g. :handlebars:`@card`), if present. This matches how Fractal
provides component contexts. With :handlebars:`merge=true`, the custom context
is merged into this default context instead of replacing it.

..  _templates-helpers-get:

get
---

Reads a property path from an object or array. Unlike plain dot notation, it
supports getter methods and dynamic keys. Paths are resolved the same way as
:ref:`variable paths in Fluid templates <fluid:variable-access-objects>`:

..  code-block:: handlebars

    {{get post "category.title"}}
    {{get object dynamicKey}}

..  _templates-helpers-join:

join
----

Joins all given values into a string. Values that cannot be converted to a
string are skipped:

..  code-block:: handlebars

    {{join firstName lastName separator=" "}}

..  _templates-helpers-merge:

merge
-----

Merges arrays recursively, with later arrays overriding earlier ones. Hash
arguments are merged last. Mostly used as a subexpression to build a context
for a partial:

..  code-block:: handlebars

    {{> @card (merge item highlighted=true)}}

..  _templates-helpers-debug:

debug
-----

Dumps a value using Extbase's :php:`DebuggerUtility`. Without argument, the
current context is dumped:

..  code-block:: handlebars

    {{debug}}
    {{debug item title="Current item" maxDepth=3}}

..  _templates-helpers-view-helper:

viewHelper, viewHelperNamespace
-------------------------------

Invokes a Fluid ViewHelper. This is meant as a temporary aid when
:ref:`migrating from Fluid <migration-from-fluid-helpers-bridge>`:

..  code-block:: handlebars

    {{viewHelper "f:format.date" date=someDate format="d.m.Y"}}

..  _templates-debugging:

Debugging tips
==============

*   Use :handlebars:`{{debug}}` to inspect the available variables.
*   Enable :ref:`rendering.strictMode <extension-configuration-rendering-strict-mode>`
    during development to get an exception for missing variables instead of
    empty output.
*   Compiled templates are cached. Flush caches if template changes don't
    show up.
