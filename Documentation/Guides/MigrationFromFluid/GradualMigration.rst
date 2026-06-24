..  include:: /Includes.rst.txt

..  _migration-from-fluid-gradual:

================
Gradual migration
================

Replacing every Fluid template in one step is rarely practical. This page
describes an incremental approach that lets Fluid and Handlebars coexist in
the same TYPO3 installation — even within the same extension — so you can
migrate one content element or controller at a time.

..  _migration-from-fluid-gradual-overview:

Strategy overview
=================

The recommended path has three lanes that can be worked independently:

*   **Content elements** rendered via :typoscript:`tt_content.*` TypoScript: Replace
    the content object type from :typoscript:`FLUIDTEMPLATE` to
    :typoscript:`HANDLEBARSTEMPLATE` one CType at a time.
*   **Extbase controllers**: Extend :php:`HandlebarsController`, then migrate templates
    action by action with a Fluid fallback in place.
*   **Shared partials / layout**: Migrate the layout shell last, once all
    templates that depend on it have been converted.

..  rst-class:: bignums

#.  **Install and configure paths**

    Install the extension and include the base site set without the
    content-element set. The content-element set replaces
    :typoscript:`lib.contentElement` globally; omitting it keeps all existing content
    elements on Fluid.

    ..  code-block:: yaml
        :caption: config/sites/<site>/config.yaml

        dependencies:
          - cpsit/handlebars   # base set only — does NOT touch lib.contentElement

    Configure template and partial root paths for Handlebars independently of
    the Fluid paths:

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

    Fluid and Handlebars path registrations are completely independent, so
    there is no risk of one system picking up the other's files.

#.  **Migrate content elements one at a time**

    For each content element, convert the TypoScript definition from
    :typoscript:`FLUIDTEMPLATE` to :typoscript:`HANDLEBARSTEMPLATE` and create
    the corresponding :file:`.hbs` file.

    **Before (Fluid):**

    ..  code-block:: typoscript

        tt_content.tx_myext_teaser = FLUIDTEMPLATE
        tt_content.tx_myext_teaser {
            templateName = Teaser
            templateRootPaths.10 = EXT:my_extension/Resources/Private/Templates
            partialRootPaths.10 = EXT:my_extension/Resources/Private/Partials
            layoutRootPaths.10 = EXT:my_extension/Resources/Private/Layouts
            variables {
                title = TEXT
                title.field = header
            }
            dataProcessing {
                10 = TYPO3\CMS\Frontend\DataProcessing\FilesProcessor
                10 {
                    references.fieldName = image
                    as = images
                }
            }
        }

    **After (Handlebars):**

    ..  code-block:: typoscript

        tt_content.tx_myext_teaser = HANDLEBARSTEMPLATE
        tt_content.tx_myext_teaser {
            templateName = Teaser
            templateRootPaths.10 = EXT:my_extension/Resources/Private/Templates/Handlebars
            partialRootPaths.10 = EXT:my_extension/Resources/Private/Partials/Handlebars
            variables {
                title = TEXT
                title.field = header
            }
            dataProcessing {
                10 = files
                10 {
                    references.fieldName = image
                    as = images
                }
            }
        }

    All other :typoscript:`tt_content.*` definitions that are not yet migrated
    continue to use Fluid without any changes.

#.  **Migrate Extbase controllers**

    For controller-based rendering, the extension provides
    :php:`HandlebarsViewFactory`, which replaces TYPO3's default
    :php:`FluidViewFactory`. It inspects the TypoScript configuration and returns
    a :php:`HandlebarsView` when Handlebars configuration is present, or falls
    back to the Fluid view when it is not.

    ..  note::
        The view factory is globally injected to all Extbase controllers extending
        :php:`ActionController`, so there's no need to switch the view factory injection.

    **Extend HandlebarsController:**

    For controllers you own, extend
    :php:`CPSIT\Typo3Handlebars\Controller\HandlebarsController` instead of
    :php:`ActionController`. The :php:`renderView()` method renders the current
    action via Handlebars:

    ..  code-block:: php
        :caption: EXT:my_extension/Classes/Controller/BlogController.php

        namespace Vendor\Extension\Controller;

        use CPSIT\Typo3Handlebars\Controller\HandlebarsController;
        use Psr\Http\Message\ResponseInterface;

        final class BlogController extends HandlebarsController
        {
            public function listAction(): ResponseInterface
            {
                $this->view->assign('posts', $this->postRepository->findAll());

                return $this->htmlResponse($this->renderView());
            }
        }

    The view factory resolves the template name automatically from the
    controller alias and action name (e.g., :file:`Blog/list.hbs` for
    :php:`listAction`). Declare the path in TypoScript:

    ..  code-block:: typoscript

        plugin.tx_myextension {
            view {
                templateRootPaths.10 = EXT:my_extension/Resources/Private/Templates/Handlebars
                partialRootPaths.10 = EXT:my_extension/Resources/Private/Partials/Handlebars
            }
        }

    **Using the Fluid fallback during migration:**

    When a controller action has not yet been ported, call
    :php:`delegateRendering()` on the view to delegate to the underlying Fluid
    view. This keeps the action working while the template is being migrated:

    ..  code-block:: php

        public function legacyAction(): ResponseInterface
        {
            $this->view->assign('items', $this->repository->findAll());

            // Render with Fluid until the .hbs template is ready
            $content = $this->view instanceof HandlebarsView
                ? $this->view->delegateRendering()
                : $this->view->render();

            return $this->htmlResponse((string)$content);
        }

#.  **Migrate the page layout shell**

    The layout shell (the outer HTML document, header, navigation, footer) is
    typically the last thing to migrate because it is shared by every page.
    Keep the existing Fluid layout in place until all content elements and
    actions that reference it have been converted to Handlebars partials.

    Once all consumers are migrated, convert the Fluid layout to a Handlebars
    layout partial using the :handlebars:`extend` / :handlebars:`block` /
    :handlebars:`content` pattern described in :ref:`migration-from-fluid-layouts`.

#.  **Switch the lib.contentElement default**

    After every content element has been migrated to :typoscript:`HANDLEBARSTEMPLATE`,
    include the :yaml:`cpsit/handlebars-content-element` site set. This sets
    :typoscript:`lib.contentElement = HANDLEBARSTEMPLATE` globally so that
    newly created content elements start from Handlebars automatically:

    ..  code-block:: yaml
        :caption: config/sites/<site>/config.yaml

        dependencies:
          - cpsit/handlebars
          - cpsit/handlebars-content-element

..  seealso::

    *   :ref:`quick-start` — minimal working setup from scratch
    *   :ref:`migration-from-fluid-layouts` — layout and section migration
    *   :ref:`migration-from-fluid-helpers` — porting ViewHelpers
