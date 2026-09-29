..  include:: /Includes.rst.txt

..  _introduction:

============
Introduction
============

..  _what-it-does:

What does it do?
================

The extension provides a full rendering environment for Handlebars templates
within TYPO3 CMS. All `core features <https://handlebarsjs.com/guide/>`_ of
Handlebars.js are supported by the usage of the third-party library
`PHP Handlebars <https://github.com/devtheorem/php-handlebars>`_.

Its main use is to seamlessly integrate Handlebars templates into TYPO3 without
the need to modify these templates again for output in TYPO3. This makes it a
good fit for projects whose templates are developed in a frontend component
library such as `Fractal <https://fractal.build/>`__ or
`Storybook <https://storybook.js.org/>`__.

..  _how-it-works:

How does it work?
=================

Rendering is configured in TypoScript, in the same way as with
:typoscript:`FLUIDTEMPLATE`:

#.  A :ref:`HANDLEBARSTEMPLATE <content-object>` content object defines which
    template to render, e.g. for a content element.
#.  Its :typoscript:`variables` and :typoscript:`dataProcessing` configuration
    prepare the data passed to the template, using TYPO3's content objects
    and :ref:`data processors <data-processors>`.
#.  The template name is resolved to a :file:`.hbs` file within the
    configured :ref:`template paths <template-paths>`.
#.  The template is compiled (and cached), rendered with the prepared
    variables and returned as output.

Extbase plugins can render Handlebars templates as well, see
:ref:`extbase-plugin`.

..  _features:

Features
========

-   **Templating engine:** Full Handlebars rendering environment for TYPO3
-   **TypoScript integration:** :typoscript:`HANDLEBARSTEMPLATE` content object
    and additional data processors to prepare template variables
-   **Component libraries:** Fractal-compatible template names (:handlebars:`@name`,
    :handlebars:`@name--variant`)
-   **Layouts:** Layout inheritance with :handlebars:`extend`, :handlebars:`block`
    and :handlebars:`content` helpers
-   **Custom helpers:** Custom helpers with auto-registration via PHP attributes
-   **Extbase support:** Controller-based rendering via :php:`HandlebarsView`
-   **Caching:** Integration with TYPO3's cache framework for compiled templates
-   **Extensibility:** PSR-14 events and replaceable services for all parts of
    the rendering pipeline
-   **Compatibility:** Compatible with TYPO3 13.4 LTS and 14.3 LTS

..  _support:

Support
=======

There are several ways to get support for this extension:

* Slack: https://typo3.slack.com/archives/C0281DBRFCZ
* GitHub: https://github.com/CPS-IT/handlebars/issues

..  _security-policy:

Security Policy
===============

Please read our `security policy <https://github.com/CPS-IT/handlebars/blob/main/SECURITY.md>`__
if you discover a security vulnerability in this extension.

..  _license:

License
=======

This extension is licensed under
`GNU General Public License 2.0 (or later) <https://www.gnu.org/licenses/old-licenses/gpl-2.0.html>`_.
