..  include:: /Includes.rst.txt

..  _migration-from-fluid-syntax:

===============
Template syntax
===============

This page provides side-by-side examples of the most common Fluid constructs
and their Handlebars counterparts. The examples assume a content element with
the variables :typoscript:`header`, :typoscript:`bodytext`, :typoscript:`items`,
and :typoscript:`image`.

..  contents::
    :local:
    :depth: 1

..  _migration-from-fluid-syntax-output:

Outputting a variable
=====================

Handlebars HTML-escapes every :handlebars:`{{...}}` expression by default.

**Fluid:**

..  code-block:: html

    {header}

**Handlebars:**

..  code-block:: handlebars

    {{header}}

..  _migration-from-fluid-syntax-raw:

Raw / unescaped output
======================

Use triple braces to output a value without HTML escaping. Reserve this for
content that has already been sanitized (e.g., a :typoscript:`parseFunc`-processed
RTE field).

**Fluid:**

..  code-block:: html

    {bodytext -> f:format.raw()}

**Handlebars:**

..  code-block:: handlebars

    {{{bodytext}}}

..  _migration-from-fluid-syntax-conditions:

Conditionals
============

:handlebars:`{{#if}}` is truthy: empty strings, :php:`0`, empty arrays, and
:php:`null` are all falsy. For numeric comparisons, write a helper (see
:ref:`migration-from-fluid-helpers`).

**Fluid:**

..  code-block:: html

    <f:if condition="{header}">
        <h1>{header}</h1>
    </f:if>

    <f:if condition="{showTeaser}">
        <f:then><p>{teaser}</p></f:then>
        <f:else><p>{fallback}</p></f:else>
    </f:if>

**Handlebars:**

..  code-block:: handlebars

    {{#if header}}
        <h1>{{header}}</h1>
    {{/if}}

    {{#if showTeaser}}
        <p>{{teaser}}</p>
    {{else}}
        <p>{{fallback}}</p>
    {{/if}}

Use :handlebars:`{{#unless}}` as shorthand for a negated :handlebars:`{{#if}}`
without an else branch:

**Fluid:**

..  code-block:: html

    <f:if condition="{hideDate}">
        <f:else><time>…</time></f:else>
    </f:if>

**Handlebars:**

..  code-block:: handlebars

    {{#unless hideDate}}
        <time>…</time>
    {{/unless}}

..  _migration-from-fluid-syntax-loops:

Loops
=====

Inside :handlebars:`{{#each}}`, :handlebars:`{{this}}` refers to the current
item and :handlebars:`@index` holds the zero-based iteration counter.
:handlebars:`@first` and :handlebars:`@last` are boolean flags for the
boundary items.

**Fluid:**

..  code-block:: html

    <f:for each="{items}" as="item" iteration="loop">
        <li class="{f:if(condition: loop.isFirst, then: 'is-first')}">
            {item.title}
        </li>
    </f:for>

**Handlebars:**

..  code-block:: handlebars

    {{#each items}}
        <li{{#if @first}} class="is-first"{{/if}}>
            {{this.title}}
        </li>
    {{/each}}

Nested :handlebars:`{{#each}}` blocks access the parent scope via :handlebars:`../`:

..  code-block:: handlebars

    {{#each categories}}
        <h2>{{this.title}}</h2>
        {{#each this.items}}
            <p>{{this.label}} (category: {{../title}})</p>
        {{/each}}
    {{/each}}

..  _migration-from-fluid-syntax-with:

Scoping with :handlebars:`{{#with}}`
====================================

:handlebars:`{{#with}}` sets a new scope root, similar to assigning a sub-object
and then using it directly. Inside the block, properties of the given object are
accessible without a prefix.

**Fluid** (using a variable alias via f:alias):

..  code-block:: html

    <f:alias map="{addr: '{data.address}'}">
        {addr.street}, {addr.city}
    </f:alias>

**Handlebars:**

..  code-block:: handlebars

    {{#with data.address}}
        {{street}}, {{city}}
    {{/with}}

..  _migration-from-fluid-syntax-partials:

Partials
========

Handlebars partials are resolved relative to the configured partial root paths
in the same way as templates. The partial name is the filename without the
:file:`.hbs` extension.

**Fluid:**

..  code-block:: html

    <f:render partial="Teaser" />

    <f:render partial="Card" arguments="{title: item.title, image: item.image}" />

**Handlebars:**

..  code-block:: handlebars

    {{> Teaser}}

    {{> Card title=item.title image=item.image}}

To pass the entire current context to the partial (as Fluid does with the
:fluid:`arguments="{_all}"` attribute), just omit any arguments:

..  code-block:: handlebars

    {{> Teaser}}

To pass a completely different context object, provide it as a positional
argument before any hash arguments:

..  code-block:: handlebars

    {{> Card item}}

..  _migration-from-fluid-syntax-dynamic-access:

Dynamic property access
=======================

Handlebars dot-path notation resolves nested public properties:
:handlebars:`{{user.address.city}}`. For getter resolution (using Extbase's
:php:`ObjectAccess`) and dynamic key lookups (where the key itself is a variable),
use the built-in :php:`get` helper:

**Fluid:**

..  code-block:: html

    {object.{dynamicKey}}
    {object.privateProperty.arrayKey}

**Handlebars:**

..  code-block:: handlebars

    {{get object dynamicKey}}
    {{get object 'privateProperty[arrayKey]'}}

..  _migration-from-fluid-syntax-comments:

Comments
========

Handlebars comments are stripped from the rendered output and never appear in
the HTML source. Use them for template-internal notes.

**Fluid:**

..  code-block:: html

    <!-- this comment appears in HTML source -->

**Handlebars:**

..  code-block:: handlebars

    {{!-- this comment is stripped from the output --}}

..  _migration-from-fluid-syntax-escaping:

Escaping Handlebars delimiters
==============================

To output a literal :handlebars:`{{` in the rendered HTML, use the raw
block syntax:

..  code-block:: handlebars

    {{{raw}}}}
        This {{{will not be}}} parsed as Handlebars.
    {{{{/raw}}}}
