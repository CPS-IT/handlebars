..  include:: /Includes.rst.txt

..  _extbase-plugin:

===============
Extbase plugins
===============

:php:`HandlebarsViewFactory` integrates Handlebars rendering into the Extbase
MVC stack. It implements :php:`TYPO3\CMS\Core\View\ViewFactoryInterface` and
is wired globally, so every Extbase controller that goes through the standard
view factory mechanism automatically benefits from it without any code changes.

When the factory detects an Extbase request it reads the
:typoscript:`handlebars` key from the plugin's TypoScript configuration and
returns a :php:`HandlebarsView`. If no :typoscript:`handlebars` key is present
and the controller does not extend :php:`HandlebarsController`, the factory
falls back to the Fluid view.

..  contents::
    :local:
    :depth: 1

..  _extbase-plugin-typoscript:

TypoScript configuration
========================

Per-plugin Handlebars configuration lives under the :typoscript:`handlebars`
key in the plugin's TypoScript configuration:

..  code-block:: typoscript

    plugin.tx_myextension_myplugin {
        handlebars {
            default {
                templateRootPaths.10 = EXT:my_extension/Resources/Private/Templates/Handlebars
                partialRootPaths.10 = EXT:my_extension/Resources/Private/Partials/Handlebars
            }
        }
    }

..  _extbase-plugin-typoscript-keys:

Resolution keys
---------------

The :typoscript:`handlebars` array supports four keys, resolved and merged
from least to most specific so that narrower entries override broader ones:

+-----------------------------------------------+--------------------------------------------------+
| Key                                           | Applies to                                       |
+===============================================+==================================================+
| :typoscript:`default`                         | All controllers in the plugin                    |
+-----------------------------------------------+--------------------------------------------------+
| :typoscript:`<ControllerAlias>`               | One controller, all actions                      |
+-----------------------------------------------+--------------------------------------------------+
| :typoscript:`<ControllerAlias>::<actionName>` | One controller, one action                       |
+-----------------------------------------------+--------------------------------------------------+
| :typoscript:`<ControllerFQCN>`                | Controller matched by fully-qualified class name |
+-----------------------------------------------+--------------------------------------------------+

..  code-block:: typoscript

    plugin.tx_myextension_myplugin {
        handlebars {
            default {
                templateRootPaths.10 = EXT:my_extension/Resources/Private/Templates/Handlebars
                partialRootPaths.10 = EXT:my_extension/Resources/Private/Partials/Handlebars
            }
            Blog {
                templateRootPaths.20 = EXT:my_extension/Resources/Private/Templates/Blog
            }
            Blog::list {
                templateName = @blog-list
            }
        }
    }

The controller alias matches the value registered in
:php:`ExtensionUtility::configurePlugin()`. For the example above,
:php:`BlogController` would typically have alias :typoscript:`Blog`.

..  _extbase-plugin-typoscript-properties:

Properties
----------

Each resolution key accepts the same properties as a
:ref:`HANDLEBARSTEMPLATE <content-object>` content object:

+----------------------------------------------+---------+-------------------------------------------+
| Property                                     | Type    | Description                               |
+==============================================+=========+===========================================+
| :typoscript:`templateName`                   | string  | Template name or ``@``-prefixed flat name.|
|                                              |         | Defaults to                               |
|                                              |         | :typoscript:`<ControllerAlias>/<action>`. |
+----------------------------------------------+---------+-------------------------------------------+
| :typoscript:`format`                         | string  | File extension. Defaults to ``hbs``.      |
+----------------------------------------------+---------+-------------------------------------------+
| :typoscript:`templateRootPaths`              | array   | Additional template root paths.           |
+----------------------------------------------+---------+-------------------------------------------+
| :typoscript:`partialRootPaths`               | array   | Additional partial root paths.            |
+----------------------------------------------+---------+-------------------------------------------+
| :typoscript:`variables`                      | array   | Extra variables passed to the template.   |
+----------------------------------------------+---------+-------------------------------------------+

..  _extbase-plugin-default-template:

Default template name
=====================

When no :typoscript:`templateName` is configured, the factory derives one
automatically from the controller alias and action name:

..  code-block:: text

    <ControllerAlias>/<actionName>

For :php:`BlogController::listAction()` with alias :typoscript:`Blog` this
resolves to :file:`Blog/list.hbs` under the configured template root paths.

..  _extbase-plugin-handlebars-controller:

HandlebarsController
====================

For controllers you own, extend
:php:`CPSIT\Typo3Handlebars\Controller\HandlebarsController` instead of
:php:`ActionController`. This guarantees Handlebars rendering even when no
:typoscript:`handlebars` TypoScript key is present — the factory always returns
a :php:`HandlebarsView` for these controllers:

..  code-block:: php
    :caption: EXT:my_extension/Classes/Controller/BlogController.php

    namespace Vendor\MyExtension\Controller;

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

..  _extbase-plugin-fluid-fallback:

Fluid fallback
==============

During an incremental migration you may need to keep some actions on Fluid
while others are already on Handlebars. Call :php:`delegateRendering()` on the
view to hand off to the underlying Fluid view for that action:

..  code-block:: php

    public function legacyAction(): ResponseInterface
    {
        $this->view->assign('items', $this->repository->findAll());

        $content = $this->view instanceof HandlebarsView
            ? $this->view->delegateRendering()
            : $this->view->render();

        return $this->htmlResponse((string)$content);
    }

..  seealso::

    *   :ref:`content-object` — full property reference for
        :typoscript:`HANDLEBARSTEMPLATE`
    *   :ref:`template-paths` — how template and partial root paths are
        collected and prioritised
