..  include:: /Includes.rst.txt

..  _data-processor-media:

=====
media
=====

**Class:** :php:`CPSIT\Typo3Handlebars\DataProcessing\MediaProcessor`

Resolves a file or file reference and runs it through a matching *media
processor* — a pluggable component that turns a resource into whatever
shape a template needs (for example, an image with responsive source sets).
The extension does not ship any media processors itself; see
:ref:`developer-corner-media-processor` for how to register your own.

..  contents::
    :local:
    :depth: 1

..  _data-processor-media-data-sources:

Data sources
============

:typoscript:`file` is resolved exactly like :typoscript:`object` on
:ref:`data-processor-object-access` — see :ref:`usage-data-sources-payload`
for the full syntax. It just uses a processor-specific option name instead
of the generic :typoscript:`dataSource`.

..  _data-processor-media-usage:

Usage
=====

..  code-block:: typoscript

    tt_content.textpic = HANDLEBARSTEMPLATE
    tt_content.textpic {
        templateName = @textpic

        dataProcessing {
            10 = files
            10 {
                references.fieldName = image
                as = files
            }

            20 = media
            20 {
                file = processedData:files.0
                as = image

                config {
                    image {
                        # options for a registered "image" media processor
                    }
                }
            }
        }
    }

The first file resolved by the core :typoscript:`files` processor is passed
to :typoscript:`media`, which picks whichever registered media processor's
:php:`supports()` method matches it first — here, a media processor
registered under the name :typoscript:`image` — and stores its result under
:typoscript:`image`.

..  _data-processor-media-properties:

Properties
==========

:typoscript:`file`
    Data source reference the resource is read from (see
    :ref:`data-processor-media-data-sources`). Required.

:typoscript:`as`
    Target key in the processed data array the media processor's result is
    stored under. Default: :typoscript:`result`.

:typoscript:`config.<name>`
    Configuration passed to the media processor registered under
    :typoscript:`<name>`. Only applied if that processor actually matches
    the resolved resource.

If :typoscript:`file` cannot be resolved, or no registered media processor
supports the resolved resource, a warning is logged and the processed data
is returned unchanged.
