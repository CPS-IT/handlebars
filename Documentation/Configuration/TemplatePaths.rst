..  include:: /Includes.rst.txt

..  _template-paths:

==============
Template paths
==============

Template and partial root paths are collected from various sources, each
with a distinct priority. Higher-priority sources win over lower-priority ones.
Within a single source, higher numeric keys override lower ones.

..  contents::
    :local:
    :depth: 1

..  _template-paths-priority:

Priority order
==============

+-------------------------------------------------+----------+-----------+
| Source                                          | Priority | Cacheable |
+=================================================+==========+===========+
| Per-content-object TypoScript                   | 100      | No        |
+-------------------------------------------------+----------+-----------+
| :typoscript:`plugin.tx_handlebars.view`         | 50       | Yes       |
+-------------------------------------------------+----------+-----------+
| Service container (e.g. :file:`Services.yaml`)  | 0        | Yes       |
+-------------------------------------------------+----------+-----------+

..  _template-paths-per-content-object:

Per-content-object (priority 100)
==================================

Template and partial root paths can be set directly inside a
:typoscript:`HANDLEBARSTEMPLATE` content object. These paths apply only to
that specific rendering, including any nested partial lookups triggered by it.

..  code-block:: typoscript

    tt_content.textmedia = HANDLEBARSTEMPLATE
    tt_content.textmedia {
        templateRootPaths {
            10 = EXT:my_extension/Resources/Private/Templates
        }
        partialRootPaths {
            10 = EXT:my_extension/Resources/Private/Partials
        }
    }

..  seealso::

    :ref:`content-object` for the full :typoscript:`HANDLEBARSTEMPLATE`
    property reference.

..  _template-paths-typoscript:

TypoScript (priority 50)
========================

Global paths for all renderings on the current page can be configured
under :typoscript:`plugin.tx_handlebars.view`:

..  code-block:: typoscript

    plugin.tx_handlebars {
        view {
            templateRootPaths {
                10 = EXT:my_extension/Resources/Private/Templates
            }
            partialRootPaths {
                10 = EXT:my_extension/Resources/Private/Partials
            }
        }
    }

The :typoscript:`cpsit/handlebars` site set also populates these paths
from the site settings :typoscript:`{$handlebars.view.templateRootPath}` and
:typoscript:`{$handlebars.view.partialRootPath}`.

..  note::

    When multiple extensions declare paths under the same numeric key, the last
    one loaded wins. Use distinct keys (e.g., 10, 20, 30) to ensure all paths
    are registered.

..  _template-paths-service-container:

Service container (priority 0)
================================

The lowest-priority source is the service container. Paths registered here
apply instance-wide, regardless of the current page or content object, and
serve as the global fallback.

..  code-block:: yaml
    :caption: Configuration/Services.yaml

    handlebars:
      view:
        templateRootPaths:
          10: EXT:my_extension/Resources/Private/Templates
        partialRootPaths:
          10: EXT:my_extension/Resources/Private/Partials

The :php:`HandlebarsExtension` DI extension merges all paths declared this
way into the container parameters :php:`%handlebars.templateRootPaths%` and
:php:`%handlebars.partialRootPaths%`.
