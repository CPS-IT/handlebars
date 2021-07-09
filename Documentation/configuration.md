# Configuration

## Handlebars cache

The Handlebars cache is called `handlebars` and is registered by default when installing and
activating the extension. Its cache backend is not configured and therefore uses the default
setting (database cache). You can specify a different cache backend as follows:

```php linenums="1"
# ext_localconf.php

if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['handlebars']['backend'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['handlebars']['backend']
        = \TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend::class;
}
```

!!! help
    Read more about cache configuration in the
    [official TYPO3 documentation]({{ link.t3_explained }}/ApiOverview/CachingFramework/Configuration/Index.html){: target=_blank }.

## Template root paths

See the explanation on [Register components](rendering/register-components.md#template-root-paths)
to get an overview about how to configure template and partial root paths.

!!! attention
    Make sure you have [defined all dependencies](install.md#define-dependencies). Otherwise, template
    root paths might not be interpreted correctly.
