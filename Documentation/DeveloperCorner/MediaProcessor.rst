..  include:: /Includes.rst.txt

..  _developer-corner-media-processor:

==============
MediaProcessor
==============

The :ref:`media <data-processor-media>` data processor resolves a file
resource and hands it to whichever registered media processor's
:php:`supports()` method matches it first. Implement the interface
yourself to support whatever resource kinds you need (images, documents,
videos, download links, ...).

..  contents::
    :local:
    :depth: 1

..  _developer-corner-media-processor-interface:

The interface
=============

..  php:namespace:: CPSIT\Typo3Handlebars\DataProcessing\Media

..  php:interface:: MediaProcessor

    ..  php:method:: process(contentObjectRenderer, resource, configuration = [])

        Process the given resource and return the resulting array, which is
        stored under :typoscript:`media`'s :typoscript:`as` key.

        :param ContentObjectRenderer contentObjectRenderer: The current content object renderer.
        :param resource: The resolved resource — a core
            :php:`ResourceInterface` or an Extbase :php:`File`/:php:`FileReference`.
        :param array configuration: This processor's slice of :typoscript:`config.<name>`.
        :returntype: array

    ..  php:method:: supports(resource)

        Return :php:`true` if this processor can handle the given resource.
        Called for every registered media processor, in priority order, until
        one returns :php:`true`.

        :param mixed resource: The resolved resource, of unknown type.
        :returntype: bool

..  _developer-corner-media-processor-implement:

Implement a media processor
===========================

Implementations are auto-registered because the interface itself carries
:php:`#[AutoconfigureTag('handlebars.media_processor')]`. The
:php:`#[AsTaggedItem('<name>')]` attribute on the implementing class both
determines matching order among several processors (higher priority is tried
first, default 0, same as :ref:`PathProvider and VariableProvider
<developer-corner-paths-and-variables>`) and gives the processor its
:typoscript:`<name>`, i.e. the key under which :typoscript:`media` looks
up its :typoscript:`config.<name>` block.

..  code-block:: php
    :caption: EXT:my_extension/Classes/DataProcessing/Media/DownloadProcessor.php

    namespace Vendor\Extension\DataProcessing\Media;

    use CPSIT\Typo3Handlebars\DataProcessing\Media\MediaProcessor;
    use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
    use TYPO3\CMS\Core\Resource\AbstractFile;
    use TYPO3\CMS\Core\Resource\ResourceInterface;
    use TYPO3\CMS\Extbase\Domain\Model\File;
    use TYPO3\CMS\Extbase\Domain\Model\FileReference;
    use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

    #[AsTaggedItem('download')]
    final readonly class DownloadProcessor implements MediaProcessor
    {
        public function process(
            ContentObjectRenderer $contentObjectRenderer,
            ResourceInterface|File|FileReference $resource,
            array $configuration = [],
        ): array {
            return [
                'url' => $resource->getPublicUrl(),
                'label' => $configuration['label'] ?? $resource->getName(),
            ];
        }

        public function supports(mixed $resource): bool
        {
            return $resource instanceof AbstractFile && !$resource->isImage();
        }
    }

With this registered, :typoscript:`config.download.label` becomes available
wherever :typoscript:`media` is used.

..  _developer-corner-media-processor-configurable:

Typed configuration with ConfigurableProcessor
==============================================

Mapping :typoscript:`configuration` by hand, as above, is fine for a couple
of options. For more involved configuration, extend the abstract
:php:`CPSIT\Typo3Handlebars\DataProcessing\Media\ConfigurableProcessor`
instead, which uses `cuyz/valinor <https://github.com/CuyZ/Valinor>`__ to
map the raw configuration array onto a typed, immutable configuration
object before :php:`processFile()` is called:

..  code-block:: php

    /**
     * @extends ConfigurableProcessor<MyConfiguration>
     */
    final class MyProcessor extends ConfigurableProcessor
    {
        public function processFile(
            ContentObjectRenderer $contentObjectRenderer,
            ResourceInterface|File|FileReference $resource,
            Configuration $configuration,
        ): array {
            // $configuration is an instance of MyConfiguration
        }

        public function supports(mixed $resource): bool
        {
            // ...
        }

        protected function getConfigurationClass(): string
        {
            return MyConfiguration::class;
        }
    }

:php:`MyConfiguration` only needs to implement the empty marker interface
:php:`CPSIT\Typo3Handlebars\DataProcessing\Media\Configuration\Configuration`
and declare its accepted options as constructor-promoted properties.

..  seealso::

    `MediaProcessor <https://github.com/CPS-IT/handlebars/blob/main/Classes/DataProcessing/Media/MediaProcessor.php>`__
    interface source on GitHub.
