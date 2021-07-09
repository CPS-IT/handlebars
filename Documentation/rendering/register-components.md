# Register components

The registration of components is only required for the `DataProcessor`. All associated components
are resolved automatically if they are named consistently.

## `DataProcessor`

Registration is done when building the service container using the tag `handlebars.processor`.
Therefore, `DataProcessor` components need to be configured in a `Configuration/Services.yaml` file
within a concrete TYPO3 extension:

```yaml linenums="1"
# Configuration/Services.yaml

services:
  _defaults:
    autowire: true
    autoconfigure: true
    public: false

  Fr\Sitepackage\:
    resource: '../Classes/*'

  Fr\Sitepackage\DataProcessing\TextMediaProcessor:
    tags: ['handlebars.processor']
```

Using the above configuration, the
[`DataProcessorPass`]({{ repository.blob }}/Classes/DependencyInjection/DataProcessorPass.php)
would register and apply the following components:

* DataProcessor: `Fr\Sitepackage\DataProcessing\TextMediaProcessor`
* DataProvider: `Fr\Sitepackage\Data\TextMediaProvider`
* Presenter: `Fr\Sitepackage\Presenter\TextMediaPresenter`

!!! important
    The `DataProvider` and `Presenter` components are only resolved and applied to the `DataProcessor`
    if they are not explicitly configured within the `Services.yaml` configuration yet:

    ```yaml linenums="1"
    # Configuration/Services.yaml

    Fr\Sitepackage\DataProcessing\TextMediaProcessor:
      calls:
        # Will not be overridden by DataProcessorPass
        - setPresenter: ['Fr\Sitepackage\Presenter\AlternativeTextMediaPresenter']
      tags: ['handlebars.processor']
    ```

## Template root paths

The template root paths define possible paths to directories which may contain templates to be rendered.
When defining it, all template files passed to the `Renderer` should be relative to this path. If
a template file cannot be located within the registered template root paths, an exception is thrown.
Alternatively, the full template path can be defined when rendering a template.

Registration of the template root paths is done within the `Services.yaml` file:

```yaml linenums="1"
# Configuration/Services.yaml

handlebars:
  template:
    template_root_paths:
      10: EXT:sitepackage/Resources/Private/Templates
```

!!! important
    Template root paths are processed in reversed order. This means that paths with a higher array
    key will be processed first. In case you want a root path with higher priority, you can explicitly
    set the array key like follows:

    ```yaml
    handlebars:
      template:
        template_root_paths:
          # Will be processed first due to the higher array key
          1607078923: EXT:sitepackage/Resources/Private/Templates
          10: EXT:base_extension/Resources/Private/Templates
    ```

In addition to the template paths, the partial paths must also be defined, if partials are used.
This is done via an additional parameter within the `Services.yaml` file:

```yaml linenums="1"
# Configuration/Services.yaml

handlebars:
  template:
    partial_root_paths:
      10: EXT:sitepackage/Resources/Private/Partials
```

## `Renderer`

The extension already comes with a `Renderer` called
[`HandlebarsRenderer`]({{ repository.blob }}/Classes/Renderer/HandlebarsRenderer.php).
It covers all relevant TYPO3-related configuration such as cache path or template path resolving.

In case another `Renderer` should be used instead, it can be registered within the `Services.yaml`
file:

```yaml linenums="1"
# Configuration/Services.yaml

Fr\Typo3Handlebars\Renderer\RendererInterface:
  alias: Fr\Sitepackage\Renderer\AlternativeRenderer
```

!!! important
    Note that when using a custom `Renderer` you are responsible for passing template cache and
    template resolvers to the `Renderer`. When using the default `Renderer`, this is already
    done when building the service container.

## `Helper`s

`Helper`s can be registered either by configuring them in `Services.yaml` or manually by calling
`$renderer->registerHelper()`.

### Automatic registration using the Service container

The recommended way of registering `Helper`s is by using the global Service container.
This ensures that the `Helper`s are always available in the `Renderer`. To achieve this,
add the following lines to your `Services.yaml` file:

```yaml linenums="1"
# Configuration/Services.yaml

Fr\Sitepackage\Renderer\Helper\GreetHelper:
  tags:
    - name: handlebars.helper
      identifier: 'greet'
      method: 'evaluate'
```

!!! important
    Note that it's required to tag each `Helper` with the tag name `handlebars.helper` in order
    to enable automatic registration for the `HandlebarsRenderer`.

The `identifier` configuration defines how the `Helper` should be named. It is then used in
Handlebars templates as identifier when calling the registered `Helper`.

Using `method` one defines the class method which will be used as callback in combination with
the configured class name. The above example results in registration of a new `Helper` called
`greet` which provides a callback `Fr\Sitepackage\Renderer\Helper\GreetHelper::evaluate`.

The appropriate class should look like follows:

```php linenums="1"
# Classes/Renderer/Helper/GreetHelper.php

namespace Fr\Sitepackage\Renderer\Helper;

class GreetHelper implements \Fr\Typo3Handlebars\Renderer\Helper\HelperInterface
{
    public static function evaluate(array $context): string
    {
        return sprintf('Hello, %s!', $context['hash']['name']);
    }
}
```

!!! important
    It is required to implement the `HelperInterface` as written above. Otherwise, the Service
    container won't be able to register the `Helper` properly.

An example template could look like this:

```handlebars
{{ '{{ greet name="Bob" }}' }}
```

It will result in: `Hello, Bob!`

### Manual registration

Besides the automatic registration, `Helper`s can also be registered manually at any time. For this
it is necessary to initialize the `Renderer` before. Afterwards a `Helper` can be registered with the
method `registerHelper()` and thus made available in the `Renderer`:

```php
$renderer = new \Fr\Typo3Handlebars\Renderer\HandlebarsRenderer($cache);
$renderer->registerHelper('greet', \Fr\Sitepackage\Renderer\Helper\GreetHelper::class . '::evaluate');
```
