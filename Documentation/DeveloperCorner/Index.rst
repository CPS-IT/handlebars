..  include:: /Includes.rst.txt

..  _developer-corner:

================
Developer corner
================

This section documents the extension points available to developers who need
to go beyond what TypoScript configuration alone provides. It starts with
PSR-14 events that hook into the rendering pipeline, followed by interfaces
ordered from contributing single pieces (paths, variables, media) to replacing
the entire rendering stack. Each interface page covers when to implement it,
the contract it defines, and how to wire the implementation into the service
container.

..  toctree::
    :maxdepth: 1

    Events
    PathsAndVariables
    MediaProcessor
    DataSources
    DataSourceAwareProcessor
    TemplateResolver
    Renderer
