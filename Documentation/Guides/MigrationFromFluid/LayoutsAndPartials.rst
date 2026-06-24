..  include:: /Includes.rst.txt

..  _migration-from-fluid-layouts:

====================
Layouts and partials
====================

Fluid's layout system — :fluid:`<f:layout>`, :fluid:`<f:section>`, and
:fluid:`<f:render section="...">` — is one of the first things developers look for
when switching template engines. EXT:handlebars ships an equivalent mechanism
implemented as the :handlebars:`extend`, :handlebars:`block`, and :handlebars:`content` helpers, modelled
after the `handlebars-layouts <https://github.com/shannonmoeller/handlebars-layouts>`_
convention.

..  contents::
    :local:
    :depth: 1

..  _migration-from-fluid-layouts-concept:

How the systems compare
=======================

Fluid uses a **push** model: a child template declares which layout it inherits
and then *pushes* named sections into the layout's named slots. Handlebars uses
the same model, but the building blocks are ordinary helpers rather than
dedicated language constructs.

+------------------------------------------------+--------------------------------------------------------+
| Fluid                                          | Handlebars                                             |
+================================================+========================================================+
| :fluid:`<f:layout name="Default" />`           | :handlebars:`{{#extend "default"}} … {{/extend}}`      |
+------------------------------------------------+--------------------------------------------------------+
| :fluid:`<f:render section="Main">` (in layout) | :handlebars:`{{#block "main"}} … {{/block}}`           |
+------------------------------------------------+--------------------------------------------------------+
| :fluid:`<f:section name="Main">` (in template) | :handlebars:`{{#content "main"}} … {{/content}}`       |
+------------------------------------------------+--------------------------------------------------------+

The :handlebars:`content` helper supports an optional :handlebars:`mode` hash argument
(:handlebars:`replace` / :handlebars:`append` / :handlebars:`prepend`) that controls
how the child's content is merged with the layout block's default. :handlebars:`replace`
is the default and matches Fluid's behaviour.

..  _migration-from-fluid-layouts-example:

Side-by-side example
====================

**Layout file — Fluid**:

..  code-block:: html
    :caption: EXT:my_extension/Resources/Private/Layouts/Default.html

    <!DOCTYPE html>
    <html>
    <head>
        <f:render section="Head" optional="true" />
    </head>
    <body>
        <header>
            <f:render section="Header" />
        </header>
        <main>
            <f:render section="Main" />
        </main>
        <footer>
            <f:render section="Footer" optional="true" />
        </footer>
    </body>
    </html>

**Layout file — Handlebars**:

..  code-block:: handlebars
    :caption: EXT:my_extension/Resources/Private/Partials/default.hbs

    <!DOCTYPE html>
    <html>
    <head>
        {{#block "head"}}{{/block}}
    </head>
    <body>
        <header>
            {{#block "header"}}{{/block}}
        </header>
        <main>
            {{#block "main"}}{{/block}}
        </main>
        <footer>
            {{#block "footer"}}{{/block}}
        </footer>
    </body>
    </html>

----

**Child template — Fluid**:

..  code-block:: html
    :caption: EXT:my_extension/Resources/Private/Templates/MyElement.html

    <f:layout name="Main" />

    <f:section name="Head">
        <title>{header}</title>
    </f:section>

    <f:section name="Header">
        <h1>{header}</h1>
    </f:section>

    <f:section name="Main">
        <p>{bodytext}</p>
    </f:section>

**Child template — Handlebars**:

..  code-block:: handlebars
    :caption: EXT:my_extension/Resources/Private/Templates/my-element.hbs

    {{#extend "Main"}}
        {{#content "head"}}
            <title>{{header}}</title>
        {{/content}}

        {{#content "header"}}
            <h1>{{header}}</h1>
        {{/content}}

        {{#content "main"}}
            <p>{{bodytext}}</p>
        {{/content}}
    {{/extend}}

..  note::
    Handlebars layouts are resolved as *partials*, so the layout file must be
    placed in one of the configured partial root paths, not in the template root
    paths. By convention, :file:`default.hbs` lives under
    :file:`Resources/Private/Partials/`.

..  _migration-from-fluid-layouts-default-content:

Default content in blocks
==========================

A :handlebars:`{{#block}}` can hold default markup that is used verbatim when
the child provides no matching :handlebars:`{{#content}}` for that slot. This is
the equivalent of Fluid's :fluid:`optional="true"` on :fluid:`<f:render section="...">`
combined with a fallback in the layout.

..  code-block:: handlebars
    :caption: EXT:my_extension/Resources/Private/Partials/Main.hbs

    <footer>
        {{#block "footer"}}
            <p>&copy; {{year}} My Site</p>
        {{/block}}
    </footer>

A child template that does not declare a :handlebars:`{{#content "footer"}}` block
will render the default copyright line automatically.

..  _migration-from-fluid-layouts-append-prepend:

Appending and prepending content
=================================

The :handlebars:`mode` argument lets a child *add* to a block rather than replace
it. This has no direct Fluid equivalent and is often used for accumulating
:html:`<script>` or :html:`<link>` tags:

..  code-block:: handlebars
    :caption: EXT:my_extension/Resources/Private/Templates/my-element.hbs

    {{#extend "Main"}}
        {{#content "head" mode="append"}}
            <link rel="stylesheet" href="/assets/my-element.css">
        {{/content}}

        {{#content "main"}}
            …
        {{/content}}
    {{/extend}}

..  _migration-from-fluid-layouts-partials-only:

Using partials without a layout
================================

Not every template needs a full layout. Reusable snippets that were Fluid
partials map directly to Handlebars partials with no extra ceremony — just
create a :file:`.hbs` file in the partial root path and include it with
:handlebars:`{{> Name}}`.

..  seealso::

    *   :ref:`migration-from-fluid-syntax-partials` — partial inclusion syntax
    *   :ref:`template-paths` — how to configure partial root paths
