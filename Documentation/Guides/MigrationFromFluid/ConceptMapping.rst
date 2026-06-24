..  include:: /Includes.rst.txt

..  _migration-from-fluid-concept-mapping:

===============
Concept mapping
===============

Fluid and Handlebars share the same goal — separating presentation from logic —
but take different philosophical stances on *how much* the template language
itself should do. Understanding this difference up front makes the rest of the
migration straightforward.

..  contents::
    :local:
    :depth: 1

..  _migration-from-fluid-concept-philosophy:

The logic-less philosophy
=========================

Fluid templates can contain complex expressions: inline ViewHelper chaining,
boolean operators, type-coercing comparisons, and dynamic dispatch to arbitrary
PHP classes. This power comes at the cost of templates that are hard to read
outside of a PHP context.

Handlebars is explicitly "logic-less". Templates may only output values, iterate
over arrays, and branch on truthiness. All other logic must live in a named
**helper** — a PHP callable registered with the renderer. This constraint keeps
templates readable by designers and front-end developers who do not know PHP,
and it shifts complexity to a layer that can be unit-tested cleanly.

..  _migration-from-fluid-concept-table:

Concept-by-concept mapping
===========================

The table below maps every major Fluid concept to its Handlebars equivalent.
Detailed examples for each row are given in the linked pages.

+---------------------------------------------------------------+------------------------------------------------------------+-----------------------------------------+
| Fluid                                                         | Handlebars                                                 | See                                     |
+===============================================================+============================================================+=========================================+
| :fluid:`{variable}`                                           | :handlebars:`{{variable}}` (HTML-escaped)                  | :ref:`migration-from-fluid-syntax`      |
+---------------------------------------------------------------+------------------------------------------------------------+-----------------------------------------+
| :fluid:`{variable -> f:format.raw()}`                         | :handlebars:`{{{variable}}}` (triple-stash, raw)           | :ref:`migration-from-fluid-syntax`      |
+---------------------------------------------------------------+------------------------------------------------------------+-----------------------------------------+
| :fluid:`<f:if condition="...">`, `<f:else>`                   | :handlebars:`{{#if ...}}`, `{{else}}`, `{{#unless}}`       | :ref:`migration-from-fluid-syntax`      |
+---------------------------------------------------------------+------------------------------------------------------------+-----------------------------------------+
| :fluid:`<f:for each="{list}" as="item">`                      | :handlebars:`{{#each list}}`, `{{this}}`, `@index`         | :ref:`migration-from-fluid-syntax`      |
+---------------------------------------------------------------+------------------------------------------------------------+-----------------------------------------+
| :fluid:`<f:render partial="Name" />`                          | :handlebars:`{{> Name}}`                                   | :ref:`migration-from-fluid-syntax`      |
+---------------------------------------------------------------+------------------------------------------------------------+-----------------------------------------+
| :fluid:`<f:render partial="Name" arguments="{key: value}" />` | :handlebars:`{{> Name key=value}}`                         | :ref:`migration-from-fluid-syntax`      |
+---------------------------------------------------------------+------------------------------------------------------------+-----------------------------------------+
| :fluid:`<f:layout name="Main" />`                             | :handlebars:`{{#extend "Main"}} … {{/extend}}`             | :ref:`migration-from-fluid-layouts`     |
+---------------------------------------------------------------+------------------------------------------------------------+-----------------------------------------+
| :fluid:`<f:section name="Main">` (in layout)                  | :handlebars:`{{#block "main"}} … {{/block}}`               | :ref:`migration-from-fluid-layouts`     |
+---------------------------------------------------------------+------------------------------------------------------------+-----------------------------------------+
| :fluid:`<f:section name="Main">` (in template)                | :handlebars:`{{#content "main"}} … {{/content}}`           | :ref:`migration-from-fluid-layouts`     |
+---------------------------------------------------------------+------------------------------------------------------------+-----------------------------------------+
| ViewHelper class                                              | Helper callable + :php:`#[AsHelper]` attribute             | :ref:`migration-from-fluid-helpers`     |
+---------------------------------------------------------------+------------------------------------------------------------+-----------------------------------------+
| :fluid:`<f:translate key="..." />`                            | Custom :php:`translate` helper                             | :ref:`migration-from-fluid-helpers`     |
+---------------------------------------------------------------+------------------------------------------------------------+-----------------------------------------+
| :fluid:`<f:format.date format="..." />`                       | Custom :php:`formatDate` helper                            | :ref:`migration-from-fluid-helpers`     |
+---------------------------------------------------------------+------------------------------------------------------------+-----------------------------------------+
| Variables from controller :php:`assign()`                     | TypoScript :typoscript:`variables` block                   | :ref:`migration-from-fluid-gradual`     |
+---------------------------------------------------------------+------------------------------------------------------------+-----------------------------------------+
| :typoscript:`plugin.tx_myext.view.templateRootPaths`          | :typoscript:`plugin.tx_handlebars.view.templateRootPaths`  | :ref:`template-paths`                   |
+---------------------------------------------------------------+------------------------------------------------------------+-----------------------------------------+

..  seealso::
    `Handlebars Language Guide <https://handlebarsjs.com/guide/>`__ to explore
    more available language features.

..  _migration-from-fluid-concept-no-equivalent:

Things Handlebars does not have
================================

Some Fluid features have no direct equivalent and require a different approach:

**Inline ViewHelper chains**
    Fluid allows :fluid:`{value -> f:format.trim() -> f:format.upper()}`. In
    Handlebars, compose the same logic in a single helper that applies
    both transformations.

**Arithmetic and boolean operators in templates**
    Fluid supports :fluid:`{a + b}` and :fluid:`{a && b}` in some contexts. In
    Handlebars, compute the result in a data processor or a helper and expose it
    as a plain variable.

**Type-aware comparisons**
    :fluid:`<f:if condition="{count} > 0">` works in Fluid because it parses the
    expression. Handlebars :handlebars:`{{#if count}}` only tests truthiness —
    :fluid:`0` and the empty string are falsy, everything else is truthy. Write a
    helper if you need a numeric comparison.

**Named format strings in the template**
    Fluid's :fluid:`<f:format.*>` ViewHelpers apply a PHP function to a value.
    Replace each one with a small helper whose name describes the transformation
    (e.g., :handlebars:`{{formatDate date format="d.m.Y"}}`).

..  note::
    The extension ships a :handlebars:`viewHelper` bridge helper that lets you
    call any registered Fluid ViewHelper from a Handlebars template without
    writing a wrapper. This is intended as a temporary aid during migration;
    see :ref:`migration-from-fluid-helpers-bridge` for details.
