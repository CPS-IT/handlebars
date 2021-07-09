# Usage

The following example describes how to render a single content element using a Handlebars template.
The standard content element `header` is used as an example. It is assumed that an extension
called `sitepackage` is used.

## 1. Template root paths

First of all the root paths to all relevant templates and partials must be defined in the
`Configuration/Services.yaml` file:

```yaml linenums="1"
# Configuration/Services.yaml

services:
  _defaults:
    autowire: true
    autoconfigure: true
    public: false

  Fr\Sitepackage\:
    resource: '../Classes/*'

handlebars:
  template:
    template_root_paths:
      10: 'EXT:sitepackage/Resources/Private/Templates'
    partial_root_paths:
      10: 'EXT:sitepackage/Resources/Private/Partials'
```

The [`HandlebarsExtension`]({{ repository.blob }}/Classes/DependencyInjection/Extension/HandlebarsExtension.php)
takes care of the template root paths. They will be merged and then added to the service
container resulting in the following parameters:

* `handlebars.template_root_paths`
* `handlebars.partial_root_paths`

You can reference those parameters in your custom service configuration to use the resolved
template root paths in your services.

!!! important
    If template root paths are defined multiple times (e.g. within various extensions
    providing Handlebars templates), the ones having the same numeric key will be
    overridden by the last defined one.

## 2. `DataProcessor`

In the second step a new `DataProcessor` must be created.

```php linenums="1"
# Classes/DataProcessing/HeaderProcessor.php

namespace Fr\Sitepackage\DataProcessing;

use Fr\Typo3Handlebars\DataProcessing\AbstractDataProcessor;

class HeaderProcessor extends AbstractDataProcessor
{
    protected function render(): string
    {
        $data = $this->provider->get($this->cObj->data);
        return $this->presenter->present($data);
    }
}
```

The new `HeaderProcessor` needs to be registered in the `Services.yaml` file:

```yaml linenums="1"
# Configuration/Services.yaml

services:
  # ...

  Fr\Sitepackage\DataProcessing\HeaderProcessor:
    tags: ['handlebars.processor']
```

!!! important
    According to [Register components](rendering/register-components.md#dataprocessor),
    all related components (`DataProvider`, `Presenter`) are now registered automatically
    if they're named consistently.

Alternatively, all `DataProcessor`s can be registered automatically:

```yaml linenums="1"
# Configuration/Services.yaml

services:
  # ...

  Fr\Sitepackage\DataProcessing\:
    resource: '../Classes/DataProcessing/**/*Processor.php'
    tags: ['handlebars.processor']
```

## 3. `DataProvider`

Next, a new `DataProvider` must be created:

```php linenums="1"
# Classes/Data/HeaderProvider.php

namespace Fr\Sitepackage\Data;

use Fr\Sitepackage\Data\Response\HeaderProviderResponse;
use Fr\Typo3Handlebars\Data\DataProviderInterface;
use Fr\Typo3Handlebars\Data\Response\ProviderResponseInterface;

class HeaderProvider implements DataProviderInterface
{
    public function get(array $data): ProviderResponseInterface
    {
        return (new HeaderProviderResponse())
            ->setHeader($data['header'])
            ->setHeaderLayout((int)$data['header_layout'])
            ->setHeaderLink($data['header_link'])
            ->setSubheader($data['subheader']);
    }
}
```

## 4. `Presenter`

To fulfill the rendering part, a new `Presenter` must be created:

```php linenums="1"
# Classes/Presenter/HeaderPresenter.php

namespace Fr\Sitepackage\Presenter;

use Fr\Typo3Handlebars\Data\Response\ProviderResponseInterface;
use Fr\Typo3Handlebars\Exception\UnableToPresentException;
use Fr\Typo3Handlebars\Presenter\AbstractPresenter;

class HeaderPresenter extends AbstractPresenter
{
    public function present(ProviderResponseInterface $data): string
    {
        if (!($data instanceof HeaderProviderResponse)) {
            throw new UnableToPresentException(
                'Received unexpected response from provider.',
                1613552315
            );
        }
        return $this->renderer->render(
            'Extensions/FluidStyledContent/Header',
            $data->toArray()
        );
    }
}
```

## 5. TypoScript configuration

To start the Handlebars rendering process for content elements of type "header", TypoScript must
be configured accordingly:

```typo3_typoscript linenums="1"
# Configuration/TypoScript/setup.typoscript

tt_content.header = USER
tt_content.header.userFunc = Fr\Sitepackage\DataProcessing\HeaderProcessor->process
```
