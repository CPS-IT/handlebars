..  include:: /Includes.rst.txt

..  _cache:

=====
Cache
=====

..  caution::
    This page is outdated and does not reflect the current state
    of the extension. It will be updated soon.

The Handlebars cache is called `handlebars` and is registered by default
when installing and activating the extension. Its cache backend is not
configured explicitly and therefore uses the default setting (database
cache).

You can specify a different cache backend as follows:

::

    # ext_localconf.php

    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['handlebars']['backend'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['handlebars']['backend']
            = \TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend::class;
    }

..  seealso::

    Read more about cache configuration in the
    :ref:`official TYPO3 documentation <t3coreapi:caching-configuration>`.
