..  include:: /Includes.rst.txt

..  _asset-management:

================
Asset management
================

The Handlebars extension integrates with TYPO3's :ref:`Asset collector <t3coreapi:asset-collector>`
to manage JavaScript and CSS assets in your frontend rendering. Assets are registered
directly through the :typoscript:`assets` configuration of a :typoscript:`HANDLEBARSTEMPLATE`
content object.

..  seealso::

    :ref:`Asset collector <t3coreapi:assets>` in the TYPO3 Core API reference —
    covers best practices, CSP/nonce usage, priority, and general troubleshooting.

..  contents:: Table of Contents
    :depth: 2
    :local:

Asset Types
===========

The AssetCollector API supports four distinct asset types, all fully supported by this extension:

1. **External JavaScript files** — link to external :file:`.js` files
2. **Inline JavaScript code** — embed JavaScript directly in the page
3. **External CSS files** — link to external :file:`.css` files
4. **Inline CSS code** — embed styles directly in the page

JavaScript files
----------------

Register external JavaScript files using the :typoscript:`javaScript` configuration:

..  code-block:: typoscript

    10 = HANDLEBARSTEMPLATE
    10 {
        templateName = MyTemplate

        assets {
            javaScript {
                my-app-script {
                    source = EXT:myext/Resources/Public/JavaScript/app.js
                    attributes {
                        async = 1
                        defer = 1
                        crossorigin = anonymous
                    }
                    options {
                        priority = 1
                        csp = 1
                    }
                }
            }
        }
    }

Inline JavaScript
-----------------

Add inline JavaScript code using :typoscript:`inlineJavaScript`:

..  code-block:: typoscript

    assets {
        inlineJavaScript {
            my-inline-script {
                source = console.log('Hello from Handlebars'); initMyApp();
                attributes {
                    type = module
                }
                options {
                    priority = 1
                }
            }
        }
    }

CSS files
---------

Register external stylesheets using the :typoscript:`css` configuration:

..  code-block:: typoscript

    assets {
        css {
            my-styles {
                source = EXT:myext/Resources/Public/Css/styles.css
                attributes {
                    media = screen and (max-width: 768px)
                }
                options {
                    priority = 1
                }
            }
        }
    }

Inline CSS
----------

Add inline styles using :typoscript:`inlineCss`:

..  code-block:: typoscript

    assets {
        inlineCss {
            critical-css {
                source = body { margin: 0; padding: 0; } .container { max-width: 1200px; }
            }
        }
    }

Configuration reference
=======================

source (required)
-----------------

:aspect:`Type`
    string

:aspect:`Description`
    Asset source. For external files, use :typoscript:`EXT:` syntax or absolute paths.
    For inline assets, provide the code directly as a string value.

    .. note::
        The source must be a direct string value. Dynamic asset sources via stdWrap
        are not supported. Use fixed paths or inline code only.

:aspect:`Example`
    ..  code-block:: typoscript

        # External JavaScript file
        source = EXT:myext/Resources/Public/JavaScript/file.js

        # External CSS file
        source = EXT:myext/Resources/Public/Css/styles.css

        # Inline JavaScript code
        source = console.log('Hello');

        # Inline CSS code
        source = body { margin: 0; }

attributes
----------

:aspect:`Type`
    array

:aspect:`Description`
    HTML attributes for the generated tag. Boolean attributes (:typoscript:`async`,
    :typoscript:`defer`, :typoscript:`disabled`) should be set to :typoscript:`1`
    to enable them.

:aspect:`JavaScript attributes`
    *   :typoscript:`async` (boolean): Load script asynchronously
    *   :typoscript:`defer` (boolean): Defer script execution
    *   :typoscript:`nomodule` (boolean): Fallback for older browsers
    *   :typoscript:`type` (string): Script type (e.g., "module")
    *   :typoscript:`crossorigin` (string): CORS setting (e.g., "anonymous")
    *   :typoscript:`integrity` (string): Subresource Integrity hash

:aspect:`CSS attributes`
    *   :typoscript:`media` (string): Media query (e.g., "screen", "print")
    *   :typoscript:`disabled` (boolean): Disable stylesheet
    *   :typoscript:`title` (string): Stylesheet title
    *   :typoscript:`crossorigin` (string): CORS setting
    *   :typoscript:`integrity` (string): Subresource Integrity hash

:aspect:`Example`
    ..  code-block:: typoscript

        attributes {
            async = 1
            defer = 1
            type = module
            crossorigin = anonymous
            integrity = sha384-abc123def456
        }

options
-------

:aspect:`Type`
    array

:aspect:`Description`
    AssetCollector-specific options that control asset rendering behaviour.

:aspect:`Available options`
    *   :typoscript:`priority` (boolean): Render before other assets (default: 0)
    *   :typoscript:`csp` (boolean): Add CSP nonce attribute (default: 0).
        Requires TYPO3 v14+.
    *   :typoscript:`useNonce` (boolean): Add CSP nonce attribute (default: 0).
        Deprecated since TYPO3 v14 — use :typoscript:`csp` instead.

:aspect:`Example`
    ..  code-block:: typoscript

        options {
            priority = 1
            csp = 1
        }
