..  include:: /Includes.rst.txt

..  _data-processor-media:

=====
media
=====

**Class:** :php:`CPSIT\Typo3Handlebars\DataProcessing\MediaProcessor`

Resolves a file or file reference and runs it through a matching *media
processor* — a pluggable component that turns a resource into whatever
shape a template needs (for example, an image with responsive source sets).
The extension ships with a single built-in media processor for images; see
:ref:`developer-corner-media-processor` for how to register your own.

..  contents::
    :local:
    :depth: 1

..  _data-processor-media-data-sources:

Data sources
============

:typoscript:`file` references the file or file reference to process, e.g.
the first file resolved by TYPO3's :typoscript:`files` processor
(:typoscript:`processedData:files/0`). See :ref:`usage-data-sources-payload`
for the syntax.

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
                file = processedData:files/0
                as = image

                config {
                    image {
                        sourceSets {
                            small {
                                maxW = 600c
                            }
                        }
                    }
                }
            }
        }
    }

The first file resolved by the core :typoscript:`files` processor is passed
to :typoscript:`media`, which picks the matching media processor — here,
the built-in :typoscript:`image` processor — and stores its result under
:typoscript:`image`.

..  tip::

    To process all files instead of only the first one, nest
    :typoscript:`media` inside a :ref:`data-processor-process-each` processor
    and use :typoscript:`file.current = 1` to reference the file currently
    being processed:

    ..  code-block:: typoscript

        20 = process-each
        20 {
            dataSource = processedData:files
            as = images

            dataProcessing {
                10 = media
                10 {
                    file.current = 1

                    config {
                        image {
                            sourceSets {
                                small {
                                    maxW = 600c
                                }
                            }
                        }
                    }
                }
            }
        }

    Since :typoscript:`as` is omitted on :typoscript:`media`, the result is
    merged into each item. The template can then iterate over all images:

    ..  code-block:: handlebars

        {{#each images}}
            <img src="{{sourceSets.small.src}}" alt="">
        {{/each}}

..  _data-processor-media-properties:

Properties
==========

:typoscript:`file`
    Data source reference the resource is read from (see
    :ref:`data-processor-media-data-sources`). Required.

:typoscript:`as`
    Target key in the processed data array the media processor's result is
    stored under. Optional — if omitted, the result is merged recursively
    into the processed data instead.

:typoscript:`config.<name>`
    Configuration passed to the media processor registered under
    :typoscript:`<name>` (e.g. :typoscript:`config.image` for the built-in
    image processor). Only applied if that processor actually matches the
    resolved resource.

If :typoscript:`file` cannot be resolved, or no registered media processor
supports the resolved resource, a warning is logged and the processed data
is returned unchanged.

..  _data-processor-media-image:

Built-in media processor: image
===============================

The built-in image processor matches any resource that is an image. It
generates one processed image per configured source set, using
:php:`ContentObjectRenderer::getImgResource()` under the hood, so each
source set accepts the same configuration as TYPO3's core :ref:`t3tsref:imgresource`
function (e.g. :typoscript:`maxW`, :typoscript:`maxH`, :typoscript:`width`,
:typoscript:`height`).

Its configuration is nested under :typoscript:`config.image` (see
:ref:`data-processor-media-properties` below) and results in the
following shape:

:typoscript:`sourceSets`
    A map of the configured source set names to their processed image data
    (:typoscript:`src`, :typoscript:`width`, :typoscript:`height`).

:typoscript:`originalFile`
    The resolved, unprocessed file (:php:`TYPO3\CMS\Core\Resource\AbstractFile`).
