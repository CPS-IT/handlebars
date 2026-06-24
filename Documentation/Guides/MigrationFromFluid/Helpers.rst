..  include:: /Includes.rst.txt

..  _migration-from-fluid-helpers:

=======
Helpers
=======

Fluid ViewHelpers and Handlebars helpers serve the same role: they bring PHP
logic into templates. The implementation model is different enough to warrant a
dedicated page.

..  contents::
    :local:
    :depth: 1

..  _migration-from-fluid-helpers-comparison:

ViewHelpers vs. Helpers
=======================

A Fluid ViewHelper is a PHP class that implements
:php:`TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper`. Arguments are
declared via :php:`initializeArguments()` and the class is resolved by its
namespace prefix (e.g., :fluid:`f:`, :fluid:`myext:`).

A Handlebars helper is any PHP callable registered via the :php: `#[AsHelper]`
attribute. Arguments reach the callable either as named hash arguments
(:handlebars:`name=value` pairs after the helper name) or as positional arguments
(bare values in order). There is no argument declaration step — the method
signature is the contract.

+-------------------------------------------+----------------------------------------------+
| Fluid ViewHelper                          | Handlebars helper                            |
+===========================================+==============================================+
| Class extending :php:`AbstractViewHelper` | Any class / method / callable                |
+-------------------------------------------+----------------------------------------------+
| Namespace prefix in template              | Plain identifier string                      |
+-------------------------------------------+----------------------------------------------+
| :php:`initializeArguments()`              | Method parameters                            |
+-------------------------------------------+----------------------------------------------+
| :php:`renderChildren()`                   | :php:`$options->fn($options->scope)`         |
+-------------------------------------------+----------------------------------------------+
| Registered via namespace import           | :php:`#[AsHelper('name')]` attribute         |
+-------------------------------------------+----------------------------------------------+

..  _migration-from-fluid-helpers-inline:

Porting an inline ViewHelper
============================

Inline ViewHelpers that transform a single value (like :fluid:`<f:format.date>`)
are the most common case. The Handlebars equivalent is a helper that receives the
value as a positional argument and returns the formatted string.

**Fluid:**

..  code-block:: html

    <f:format.date format="d.m.Y">{date}</f:format.date>

    {date -> f:format.date(format: 'd.m.Y')}

**Handlebars:**

..  code-block:: handlebars

    {{formatDate date format="d.m.Y"}}

**Helper implementation:**

..  code-block:: php
    :caption: EXT:my_extension/Classes/Renderer/Helper/FormatDateHelper.php

    namespace Vendor\Extension\Renderer\Helper;

    use CPSIT\Typo3Handlebars\Attribute\AsHelper;
    use DevTheorem\Handlebars\HelperOptions;

    #[AsHelper('formatDate')]
    final readonly class FormatDateHelper
    {
        public function __invoke(HelperOptions $options, ?\DateTimeInterface $date = null): ?string
        {
            $format = is_string($options->hash['format'] ?? null)
                ? $options->hash['format']
                : 'd.m.Y';

            return $date?->format($format);
        }
    }

..  _migration-from-fluid-helpers-block:

Porting a block / wrapping ViewHelper
======================================

ViewHelpers that wrap inner content (block ViewHelpers) correspond to
Handlebars *block helpers*. The inner content is rendered via
:php:`$options->fn($options->scope)` and the inverse (:handlebars:`{{else}}`)
branch via :php:`$options->inverse($options->scope)`.

**Fluid:**

..  code-block:: html

    <myext:ifGranted role="ADMIN">
        <a href="/admin">Admin panel</a>
    </myext:ifGranted>

**Handlebars:**

..  code-block:: handlebars

    {{#ifGranted role="ADMIN"}}
        <a href="/admin">Admin panel</a>
    {{/ifGranted}}

**Helper implementation:**

..  code-block:: php
    :caption: EXT:my_extension/Classes/Renderer/Helper/IfGrantedHelper.php

    namespace Vendor\Extension\Renderer\Helper;

    use CPSIT\Typo3Handlebars\Attribute\AsHelper;
    use DevTheorem\Handlebars\HelperOptions;
    use Vendor\Extension\Security\AccessChecker;

    #[AsHelper('ifGranted')]
    final readonly class IfGrantedHelper
    {
        public function __construct(
            private AccessChecker $accessChecker,
        ) {}

        public function __invoke(HelperOptions $options): string
        {
            $role = $options->hash['role'] ?? '';

            if ($this->accessChecker->isGranted((string)$role)) {
                return (string)$options->fn($options->scope);
            }

            return (string)$options->inverse($options->scope);
        }
    }

..  _migration-from-fluid-helpers-common:

Common ViewHelper equivalents
==============================

The table below lists frequently used Fluid ViewHelpers and how to handle them
in Handlebars templates.

+----------------------------------------+---------------------------------------------------------------------------+
| Fluid ViewHelper                       | Handlebars approach                                                       |
+========================================+===========================================================================+
| :fluid:`f:if`                          | Built-in :handlebars:`{{#if}}` / :handlebars:`{{#unless}}`                |
+----------------------------------------+---------------------------------------------------------------------------+
| :fluid:`f:for`                         | Built-in :handlebars:`{{#each}}`                                          |
+----------------------------------------+---------------------------------------------------------------------------+
| :fluid:`f:alias`                       | Built-in :handlebars:`{{#with}}`                                          |
+----------------------------------------+---------------------------------------------------------------------------+
| :fluid:`f:format.raw`                  | Triple-stash :handlebars:`{{{variable}}}`                                 |
+----------------------------------------+---------------------------------------------------------------------------+
| :fluid:`f:format.htmlspecialchars`     | Default :handlebars:`{{variable}}` (always escapes)                       |
+----------------------------------------+---------------------------------------------------------------------------+
| :fluid:`f:format.date`                 | Custom :handlebars:`formatDate` helper                                    |
+----------------------------------------+---------------------------------------------------------------------------+
| :fluid:`f:format.number`               | Custom :handlebars:`formatNumber` helper                                  |
+----------------------------------------+---------------------------------------------------------------------------+
| :fluid:`f:translate`                   | Custom :handlebars:`translate` helper (use TYPO3 API inside)              |
+----------------------------------------+---------------------------------------------------------------------------+
| :fluid:`f:uri.page`, :fluid:`f:link.*` | Custom URI helper (use :php:`UriBuilder` inside)                          |
+----------------------------------------+---------------------------------------------------------------------------+
| :fluid:`f:image`                       | Custom image helper (use TYPO3 image API inside)                          |
+----------------------------------------+---------------------------------------------------------------------------+
| :fluid:`f:render partial="…"`          | :handlebars:`{{> PartialName}}` or :handlebars:`{{render "PartialName"}}` |
+----------------------------------------+---------------------------------------------------------------------------+
| :fluid:`f:debug`                       | Built-in :handlebars:`{{debug}}` helper                                   |
+----------------------------------------+---------------------------------------------------------------------------+

..  _migration-from-fluid-helpers-bridge:

Fluid ViewHelper bridge
========================

As a temporary migration aid, the extension ships a :handlebars:`viewHelper`
helper that invokes any registered Fluid ViewHelper directly from a Handlebars
template. This lets you use existing ViewHelpers without writing a wrapper immediately.

..  code-block:: handlebars

    {{viewHelper "f:format.date" date=someDate format="d.m.Y"}}

    {{viewHelper "myext:widget.paginate" objects=items as="pagedItems"}}

    {{#viewHelper "myext:security.ifGranted" role="ADMIN"}}
        <a href="/admin">Admin panel</a>
    {{/viewHelper}}

To use ViewHelpers from a custom namespace, register the namespace first with
:handlebars:`viewHelperNamespace`:

..  code-block:: handlebars

    {{viewHelperNamespace "tx" "https://typo3.org/ns/Vendor/Extension/ViewHelpers"}}
    {{viewHelper "tx:myHelper" someArg=value}}

..  important::

    The :handlebars:`viewHelper` helper is intended as a **short-term escape
    hatch** during migration, not as a permanent pattern. It carries the overhead
    of bootstrapping a Fluid rendering context for every invocation and relies on
    internal Fluid APIs that may change. Replace it with a proper Handlebars
    helper once the migration for the affected template is complete.

..  seealso::

    :ref:`custom-helpers` — full reference for implementing and registering
    Handlebars helpers.
