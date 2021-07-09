# Components

The main rendering concept uses the following components:

* [`DataProvider` (Model)](#dataprovider-model)
* [`DataProcessor` (Controller)](#dataprocessor-controller)
* [`Presenter` (View transition)](#presenter-view-transition)
* [`Renderer` (View)](#renderer-view)
* [`Helper` (optional)](#helper-optional)
* [`TemplateResolver`](#templateresolver)

## `DataProvider` (Model)

[:octicons-link-external-16: Interface]({{ repository.blob }}/Classes/Data/DataProviderInterface.php){: target=_blank }

`DataProvider`s are used to deliver relevant data for processing by a `DataProcessor`.
The data source is irrelevant here: Data can be provided from the database as well as from
external sources such as APIs.

Thus `DataProvider`s fulfill the part of the "Model" in the MVC pattern. The supplied data
is not necessarily applicable to a specific template, but rather serves the general
applicability of all components involved in the rendering process.

## `DataProcessor` (Controller)

[:octicons-link-external-16: Interface]({{ repository.blob }}/Classes/DataProcessing/DataProcessorInterface.php){: target=_blank }

`DataProcessor`s are the entry point of the entire rendering process. They retrieve data
from the `DataProvider`, process it and forward it to the `Presenter`.

The entire processing logic takes place here. Thus `DataProcessor`s fulfill the part of
the "Controller" in the MVC pattern. They are usually addressed directly via TypoScript.

## `Presenter` (View transition)

[:octicons-link-external-16: Interface]({{ repository.blob }}/Classes/Presenter/PresenterInterface.php){: target=_blank }

In the `Presenter`, the supplied data is adapted for output in a specific template. This
template can also be selected based on the delivered data, if multiple template variants
are possible.

In the MVC pattern, the `Presenter` takes on a transition role between the `DataProcessor`
("Controller") and the `Renderer` ("View").

## `Renderer` (View)

[:octicons-link-external-16: Interface]({{ repository.blob }}/Classes/Renderer/RendererInterface.php){: target=_blank }

The processing of the template takes place in the `Renderer`. For this the template is
compiled and filled with the data from the `Presenter`. The resulting output is returned,
closing the rendering process.

The `Renderer` is thus responsible for the "View" in the context of the MVC pattern. The
compiled templates used for this are usually cached.

## `Helper` (optional)

[:octicons-link-external-16: Interface]({{ repository.blob }}/Classes/Renderer/Helper/HelperInterface.php){: target=_blank }

`Helper`s describe an easy way to bring custom PHP functionality into Handlebars templates. They are
comparable to
[ViewHelpers used in Fluid templates](https://github.com/TYPO3/Fluid/blob/master/doc/FLUID_VIEWHELPERS.md).

The default `HandlebarsRenderer` is able to handle various `Helper`s. There are only a few
limitations for the successful use of `Helper`s:

* `Helper` function must be publicly (and statically) callable
* Configured class must be loadable by a PHP class autoloader

`Helper`s play a rather subordinate role in the MVC pattern, since they are not explicitly involved in it.
However, since they are implicitly involved in the output of a template, they most likely take the role of
the "View".

## `TemplateResolver`

[:octicons-link-external-16: Interface]({{ repository.blob }}/Classes/Renderer/Template/TemplateResolverInterface.php){: target=_blank }

Whenever a template is rendered by the `Renderer`, it needs to be resolved first, e.g. by looking up the
template within all defined template root paths. It is required to define a `TemplateResolver` for each
`Renderer` as the `Renderer` itself is not able to resolve template paths by its own.

The `TemplateResolver` is also used as resolver class for partials. Since partials are not a required
part of the template rendering, defining a `TemplateResolver` for them is optional.
