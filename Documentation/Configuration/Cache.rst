..  include:: /Includes.rst.txt

..  _cache:

=====
Cache
=====

The extension registers a cache named `handlebars` that stores compiled
Handlebars templates. The cache is registered automatically on extension
activation; no manual setup is required.

The default backend is the TYPO3 database cache. To use a different backend,
add an override to your extension's :file:`ext_localconf.php` file:

..  code-block:: php
    :caption: ext_localconf.php

    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['handlebars']['backend'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['handlebars']['backend']
            = \TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend::class;
    }

..  seealso::

    :ref:`Caching in TYPO3 <t3coreapi:caching-configuration>` for all
    available backends and their configuration options.
