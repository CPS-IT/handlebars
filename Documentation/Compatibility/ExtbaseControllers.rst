..  include:: /Includes.rst.txt

..  _extbase-controllers:

===================
Extbase controllers
===================

..  versionadded:: 0.7.0

    `Feature: #18 - Provide view adapter for extbase controllers <https://github.com/CPS-IT/handlebars/pull/18>`__

In order to increase compatibility with standard extbase plugins,
the extension supports the rendering of extbase controller actions.
For this purpose, a compatibility layer was introduced, with which
own `DataProcessors` can be triggered using the :php:`HandlebarsViewResolver`.

..  _extbase-controllers-configuration:

Configuration
=============

Tag each `DataProcessor` with `handlebars.compatibility_layer` within
your :file:`Services.yaml` file and provide additional information
about the target extbase controller and actions supported by it.

..  code-block:: yaml

    # Configuration/Services.yaml

    services:
      Vendor\Extension\DataProcessing\MyProcessor:
        tags:
          - name: handlebars.processor
          - name: handlebars.compatibility_layer
            type: 'extbase_controller'
            controller: 'Vendor\Extension\Controller\MyController'
            actions: 'dummy'

The `action` configuration can be either empty (= `NULL`) or set
to a comma-separated list of action names that are supported by the
configured `DataProcessor`. If you leave it empty, the `DataProcessor`
is used for all controller actions.

..  important::

    Only `DataProcessors` that are additionally tagged with
    `handlebars.processor` are respected as component for additional
    compatibility layers.

..  _extbase-controllers-usage:

Usage
=====

Once the :php:`HandlebarsViewResolver` is triggered to render
a specific "view", it creates an array of information and passes
it to the configured `DataProcessor`. You can then take further
steps based on the provided configuration.

When accessing the :php:`$configuration` property inside your
`DataProcessor`, you should see the following properties:

::

    $configuration = [
        'extbaseViewConfiguration' => [
            'controller' => '<controller class>',
            'action' => '<controller action>',
            'request' => '<original extbase request>',
            'variables' => '<template variables>',
        ],
    ];

..  _extbase-controllers-sources:

Sources
=======

..  seealso::

    View the sources on GitHub:

    - `ExtbaseViewAdapter <https://github.com/CPS-IT/handlebars/blob/main/Classes/Compatibility/View/ExtbaseViewAdapter.php>`__
    - `HandlebarsViewResolver <https://github.com/CPS-IT/handlebars/blob/main/Classes/Compatibility/View/HandlebarsViewResolver.php>`__
    - `ExtbaseControllerCompatibilityLayer <https://github.com/CPS-IT/handlebars/blob/main/Classes/DependencyInjection/Compatibility/ExtbaseControllerCompatibilityLayer.php>`__
