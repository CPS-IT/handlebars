..  include:: /Includes.rst.txt

..  _asset-management:

================
Asset Management
================

The Handlebars extension integrates with TYPO3's modern AssetCollector API to manage
JavaScript and CSS assets in your frontend rendering. This feature allows you to
register external files and inline code directly through TypoScript configuration.

..  contents:: Table of Contents
    :depth: 2
    :local:

Overview
========

TYPO3 13+ provides the :php:`AssetCollector` service as the recommended way to register
frontend assets. The Handlebars extension fully supports this API through the
:typoscript:`assets` configuration in :typoscript:`HANDLEBARSTEMPLATE` content objects.

Benefits
--------

*   **Deduplication**: Assets with the same identifier are automatically merged across the page
*   **Priority Control**: Control rendering order with priority options
*   **CSP Support**: Automatic nonce injection for Content Security Policy compliance
*   **Modern API**: Uses TYPO3's recommended approach (not deprecated PageRenderer methods)

Asset Types
===========

The AssetCollector API supports four distinct asset types, all fully supported by this extension:

1. **External JavaScript Files** - Link to external .js files
2. **Inline JavaScript Code** - Embed JavaScript directly in the page
3. **External CSS Files** - Link to external .css files
4. **Inline CSS Code** - Embed styles directly in the page

JavaScript Files
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
                        useNonce = 1
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

CSS Files
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

Configuration Reference
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

:aspect:`JavaScript Attributes`
    *   :typoscript:`async` (boolean): Load script asynchronously
    *   :typoscript:`defer` (boolean): Defer script execution
    *   :typoscript:`nomodule` (boolean): Fallback for older browsers
    *   :typoscript:`type` (string): Script type (e.g., "module")
    *   :typoscript:`crossorigin` (string): CORS setting (e.g., "anonymous")
    *   :typoscript:`integrity` (string): Subresource Integrity hash

:aspect:`CSS Attributes`
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
    AssetCollector-specific options that control asset rendering behavior.

:aspect:`Available Options`
    *   :typoscript:`priority` (boolean): Render before other assets (default: 0)
    *   :typoscript:`useNonce` (boolean): Add CSP nonce attribute (default: 0)

:aspect:`Example`
    ..  code-block:: typoscript

        options {
            priority = 1
            useNonce = 1
        }

Complete Examples
=================

Basic Example
-------------

..  code-block:: typoscript

    page.20 = HANDLEBARSTEMPLATE
    page.20 {
        templateName = MyPage

        variables {
            title = TEXT
            title.data = page:title
        }

        assets {
            # External JavaScript
            javaScript {
                app-script {
                    source = EXT:myext/Resources/Public/JavaScript/app.js
                    attributes {
                        defer = 1
                    }
                }
            }

            # External CSS
            css {
                main-styles {
                    source = EXT:myext/Resources/Public/Css/main.css
                }
            }
        }
    }

Advanced Example with Priority
-------------------------------

..  code-block:: typoscript

    page.20 = HANDLEBARSTEMPLATE
    page.20 {
        templateName = MyPage

        assets {
            # High-priority critical CSS
            css {
                critical-styles {
                    source = EXT:myext/Resources/Public/Css/critical.css
                    options {
                        priority = 1
                    }
                }
            }

            # Regular stylesheet with media query
            css {
                responsive-styles {
                    source = EXT:myext/Resources/Public/Css/responsive.css
                    attributes {
                        media = screen and (min-width: 768px)
                    }
                }
            }

            # Inline critical CSS (highest priority)
            inlineCss {
                above-the-fold {
                    source = body { font-family: sans-serif; } h1 { font-size: 2em; }
                    options {
                        priority = 1
                    }
                }
            }

            # Modern JavaScript module
            javaScript {
                app-module {
                    source = EXT:myext/Resources/Public/JavaScript/app.js
                    attributes {
                        type = module
                    }
                    options {
                        useNonce = 1
                    }
                }
            }

            # Legacy fallback for older browsers
            javaScript {
                app-legacy {
                    source = EXT:myext/Resources/Public/JavaScript/app.legacy.js
                    attributes {
                        nomodule = 1
                        defer = 1
                    }
                }
            }

            # Inline initialization code
            inlineJavaScript {
                app-init {
                    source = window.APP_CONFIG = { apiUrl: '/api' };
                    options {
                        priority = 1
                    }
                }
            }
        }
    }

CDN Example with Security
--------------------------

..  code-block:: typoscript

    assets {
        javaScript {
            cdn-library {
                source = https://cdn.example.com/library.js
                attributes {
                    crossorigin = anonymous
                    integrity = sha384-oqVuAfXRKap7fdgcCY5uykM6+R9GqQ8K/uxy9rx7HNQlGYl1kPzQho1wx4JwY8wC
                    defer = 1
                }
            }
        }

        css {
            cdn-styles {
                source = https://cdn.example.com/styles.css
                attributes {
                    crossorigin = anonymous
                    integrity = sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN
                }
            }
        }
    }

Migration from Legacy Methods
==============================

If you're using the legacy :typoscript:`headerAssets` or :typoscript:`footerAssets`
configuration, consider migrating to the modern :typoscript:`assets` approach:

Before (Legacy)
---------------

..  code-block:: typoscript

    10 = HANDLEBARSTEMPLATE
    10 {
        templateName = MyTemplate

        headerAssets = TEXT
        headerAssets.value = <script src="path/to/script.js"></script>

        footerAssets = TEXT
        footerAssets.value = <script>console.log('footer');</script>
    }

After (Modern)
--------------

..  code-block:: typoscript

    10 = HANDLEBARSTEMPLATE
    10 {
        templateName = MyTemplate

        assets {
            javaScript {
                my-script {
                    source = EXT:myext/Resources/Public/JavaScript/script.js
                }
            }

            inlineJavaScript {
                footer-script {
                    source = console.log('footer');
                }
            }
        }
    }

Benefits of Migration
---------------------

*   Automatic asset deduplication across multiple content elements
*   Better control over rendering order via priority
*   Content Security Policy nonce support
*   Type-safe attribute handling
*   Future-proof implementation using TYPO3's recommended API

..  note::

    Legacy methods (:typoscript:`headerAssets` and :typoscript:`footerAssets`)
    remain fully supported for backward compatibility. You can use both modern
    and legacy methods in the same content object.

Best Practices
==============

Unique Identifiers
------------------

Always use unique, namespaced identifiers across your entire page to avoid conflicts:

..  code-block:: typoscript

    # Good: Namespaced identifier
    my-extension-app-script {
        source = ...
    }

    # Bad: Generic identifier (may conflict with other extensions)
    app {
        source = ...
    }

Priority Management
-------------------

Use :typoscript:`priority = 1` for assets that must load early:

..  code-block:: typoscript

    # Critical inline CSS should have priority
    inlineCss {
        critical {
            source = ...
            options.priority = 1
        }
    }

    # Regular stylesheets can have normal priority
    css {
        theme {
            source = ...
            # No priority option = loaded after priority assets
        }
    }

Content Security Policy
-----------------------

Enable :typoscript:`useNonce = 1` for inline scripts when using CSP:

..  code-block:: typoscript

    inlineJavaScript {
        inline-config {
            source = window.config = { /* ... */ };
            options.useNonce = 1
        }
    }

Module vs Classic Scripts
-------------------------

Use :typoscript:`type = module` for ES6 modules and provide :typoscript:`nomodule`
fallback for older browsers:

..  code-block:: typoscript

    # Modern browsers
    javaScript {
        app-module {
            source = EXT:myext/Resources/Public/JavaScript/app.module.js
            attributes.type = module
        }
    }

    # Legacy browsers
    javaScript {
        app-legacy {
            source = EXT:myext/Resources/Public/JavaScript/app.legacy.js
            attributes.nomodule = 1
        }
    }

Troubleshooting
===============

Assets Not Appearing
--------------------

If assets don't appear in the rendered page:

1.  **Check identifiers are unique** - Duplicate identifiers will cause the last one to win
2.  **Verify source is not empty** - Empty sources will throw an exception
3.  **Check for exceptions** - Configuration errors will halt rendering and display an error
4.  **Ensure AssetCollector is initialized** - Only works in frontend context

Duplicate Assets
----------------

If assets appear multiple times, check for:

*   Duplicate identifiers in different content objects
*   Multiple :typoscript:`HANDLEBARSTEMPLATE` objects registering the same asset

Use unique, namespaced identifiers to prevent conflicts:

..  code-block:: typoscript

    # Use extension prefix to avoid conflicts
    my-ext-my-script {
        source = ...
    }

Configuration Errors
--------------------

The extension throws :php:`InvalidAssetConfigurationException` for:

*   Unknown asset type (valid types: :typoscript:`javaScript`, :typoscript:`inlineJavaScript`, :typoscript:`css`, :typoscript:`inlineCss`)
*   Invalid asset type configuration (must be an array)
*   Invalid or empty identifier
*   Invalid asset configuration (must be an array)
*   Missing :typoscript:`source` parameter
*   Empty :typoscript:`source` value (after trimming whitespace)

These errors will halt page rendering and display an exception message. Check your TypoScript configuration to resolve the issue.

Boolean Attributes Not Working
-------------------------------

Boolean attributes require the value :typoscript:`1` (or any truthy value) to be enabled:

..  code-block:: typoscript

    # Correct
    attributes {
        async = 1        # Will output: async="async"
        defer = 1        # Will output: defer="defer"
    }

    # Incorrect - will not be output
    attributes {
        async = 0        # Omitted from output
        defer =          # Omitted from output
    }

See Also
========

*   `TYPO3 AssetCollector Documentation <https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ApiOverview/Assets/Index.html>`__
*   `TYPO3 AssetCollector API <https://api.typo3.org/13.4/classes/TYPO3-CMS-Core-Page-AssetCollector.html>`__
*   :ref:`Using the Content Object Renderer <using-the-content-object-renderer>`
