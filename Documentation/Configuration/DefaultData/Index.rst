..  include:: /Includes.rst.txt

..  _default-data:

============
Default data
============

It may happen that several (or all) templates require recurring, consistent
data. This can be, for example, paths to assets or other firmly defined
content such as e-mail addresses or names/labels/etc.

The standard `HandlebarsRenderer` provides the possibility to specify an
array :php:`$defaultData` for this purpose. This data is merged with the
concrete render data during each rendering and passed on to the `Renderer`.

..  _configure-default-data:

Configuration
=============

In your :file:`Services.yaml` file, add the following lines:

..  code-block:: yaml

    # Configuration/Services.yaml

    handlebars:
      default_data:
        publicPath: /assets
        # ...

All data will then be available as service parameter `%handlebars.default_data%`
within the service container. So you can use it everywhere you need it in
your :file:`Services.yaml` file.

..  _overwrite-default-data:

Overwrite default data
======================

If in certain cases it is necessary to overwrite a value from the default data,
it can simply be passed as an additional value in the `Presenter`:

::

    # Classes/Presenter/MyCustomPresenter.php

    namespace Vendor\Extension\Presenter;

    use Fr\Typo3Handlebars\Data\Response\ProviderResponseInterface;
    use Fr\Typo3Handlebars\Presenter\AbstractPresenter;

    class MyCustomPresenter extends AbstractPresenter
    {
        public function present(ProviderResponseInterface $data): string
        {
            $renderData = [
                // ...
            ];

            // Overwrite default data "publicPath"
            $renderData['publicPath'] = '/custom/path/to/assets';

            return $this->renderer->render('path/to/template', $renderData);
        }
    }
