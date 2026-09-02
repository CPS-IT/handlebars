..  include:: /Includes.rst.txt

..  _developer-corner-data-source-aware-processor:

========================
DataSourceAwareProcessor
========================

Implement the
:php:interface:`CPSIT\\Typo3Handlebars\\DataProcessing\\DataSource\\DataSourceAwareProcessor`
interface to run custom PHP logic during the :typoscript:`preProcessing` or
:typoscript:`postProcessing` stages of a :typoscript:`process-variables`
processor (or of :typoscript:`HANDLEBARSTEMPLATE` directly). Unlike standard
TYPO3 data processors, implementations receive a :php:`DataSourceCollection`
that gives structured access to all four data sources available at that point
in the pipeline.

..  php:namespace:: CPSIT\Typo3Handlebars\DataProcessing\DataSource

..  php:interface:: DataSourceAwareProcessor

    ..  php:method:: process(array $variables, DataSourceCollection $collection, ContentObjectRenderer $contentObjectRenderer)

        Process the current variable set and return the (modified) array.

        :param array $variables: Current template variable set.
        :param DataSourceCollection $collection: All four data sources for the current rendering.
        :param ContentObjectRenderer $contentObjectRenderer: Current content element renderer.
        :returntype: array

..  _developer-corner-data-source-aware-processor-collection:

Reading from DataSourceCollection
==================================

:php:`DataSourceCollection::resolve()` searches the data sources in priority
order and returns the first match. Pass a specific :php:`DataSource` case to
restrict the lookup:

..  code-block:: php

    use CPSIT\Typo3Handlebars\DataProcessing\DataSource\DataSource;

    // Search all sources (highest priority first)
    $table = $collection->resolve('table');

    // Search only the processor configuration
    $table = $collection->resolve('table', DataSource::ProcessorConfiguration);

    // Search two specific sources, in the given order
    $table = $collection->resolve('table', [
        DataSource::ProcessorConfiguration,
        DataSource::ContentObjectConfiguration,
    ]);

The four :php:`DataSource` cases are:

*   :php:`DataSource::ProcessorConfiguration` — this processor's own config block
*   :php:`DataSource::ProcessedData` — accumulated output from previous processors
*   :php:`DataSource::ContentObjectRenderer` — current record's field values
*   :php:`DataSource::ContentObjectConfiguration` — top-level :typoscript:`HANDLEBARSTEMPLATE` config

..  _developer-corner-data-source-aware-processor-keyword:

Resolving a keyword-configured variable
========================================

Processors often accept a configuration option whose value is itself the
name of another variable to resolve — for example, an :typoscript:`iterable`
option that names the variable to iterate over.
:php:`DataSourceCollection::resolveKeyword()` covers this pattern in one
call: it looks up :php:`$keyword` in
:php:`DataSource::ProcessorConfiguration` to obtain the variable name, then
resolves that variable name against the given (or, by default, all) data
sources. It throws
:php:`CPSIT\Typo3Handlebars\Exception\KeywordCannotBeResolved` if the
keyword is not configured or resolves to an empty string.

..  code-block:: php

    use CPSIT\Typo3Handlebars\DataProcessing\DataSource\DataSource;
    use CPSIT\Typo3Handlebars\Exception\KeywordCannotBeResolved;

    try {
        // Resolves the "iterable" processor option to a variable name,
        // then resolves that variable name against all data sources
        [$value, $variableName] = $collection->resolveKeyword('iterable');
    } catch (KeywordCannotBeResolved) {
        // The "iterable" option is not configured
    }

    // Restrict the variable lookup to a specific data source, with a default
    [$value, $variableName] = $collection->resolveKeyword(
        'iterable',
        DataSource::ProcessedData,
        [],
    );

..  _developer-corner-data-source-aware-processor-implement:

Example implementation
======================

..  code-block:: php
    :caption: EXT:my_extension/Classes/DataProcessing/MyPreProcessor.php

    namespace Vendor\Extension\DataProcessing;

    use CPSIT\Typo3Handlebars\DataProcessing\DataSource\DataSource;
    use CPSIT\Typo3Handlebars\DataProcessing\DataSource\DataSourceAwareProcessor;
    use CPSIT\Typo3Handlebars\DataProcessing\DataSource\DataSourceCollection;
    use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

    final readonly class MyPreProcessor implements DataSourceAwareProcessor
    {
        public function process(
            array $variables,
            DataSourceCollection $collection,
            ContentObjectRenderer $contentObjectRenderer,
        ): array {
            $table = $collection->resolve('table', DataSource::ProcessorConfiguration, 'tt_content');
            $variables['tableName'] = $table;

            return $variables;
        }
    }

..  _developer-corner-data-source-aware-processor-register:

Registering the processor
=========================

Reference the processor class by its fully qualified class name in
:typoscript:`preProcessing` or :typoscript:`postProcessing`:

..  code-block:: typoscript

    10 = process-variables
    10 {
        variables {
            header = TEXT
            header.field = header
        }

        preProcessing {
            10 = Vendor\Extension\DataProcessing\MyPreProcessor
        }
    }

The numeric keys control execution order when multiple processors are
registered. The class is instantiated via
:php:`GeneralUtility::makeInstance()`, so constructor injection works
as normal.
