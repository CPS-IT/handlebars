..  include:: /Includes.rst.txt

..  _developer-corner-paths-and-variables:

==============================
PathProvider & VariableProvider
==============================

Two further interfaces allow contributing template paths and global variables
from PHP rather than from TypoScript or :file:`Services.yaml` configuration.
Both are auto-registered via :php:`#[AutoconfigureTag]` and both use a
priority integer to control merge order.

..  contents::
    :local:
    :depth: 1

..  _developer-corner-path-provider:

PathProvider
============

Implement the
:php:interface:`CPSIT\\Typo3Handlebars\\Renderer\\Template\\Path\\PathProvider`
interface to contribute template and partial root paths programmatically — for
example when paths depend on the current site configuration or a value not
available at container compile time.

Higher priority values are merged last and therefore take precedence over lower
ones. The three built-in providers use 0 (:php:`GlobalPathProvider`), 50
(:php:`TypoScriptPathProvider`), and 100 (:php:`ContentObjectPathProvider`).

..  php:namespace:: CPSIT\Typo3Handlebars\Renderer\Template\Path

..  php:interface:: PathProvider

    ..  php:method:: getTemplateRootPaths()

        Return an array of template root paths, keyed by integer priority.

        :returntype: array

    ..  php:method:: getPartialRootPaths()

        Return an array of partial root paths, keyed by integer priority.

        :returntype: array

    ..  php:method:: isCacheable()

        Return :php:`true` if the paths provided by this provider may be
        cached. Return :php:`false` for request-dependent paths.

        :returntype: bool

    ..  php:method:: getPriority()

        Return the priority of this provider. Higher values win.

        :returntype: int

..  code-block:: php
    :caption: EXT:my_extension/Classes/Renderer/Template/Path/SitePathProvider.php

    namespace Vendor\Extension\Renderer\Template\Path;

    use CPSIT\Typo3Handlebars\Renderer\Template\Path\PathProvider;
    use TYPO3\CMS\Core\Site\SiteFinder;

    final readonly class SitePathProvider implements PathProvider
    {
        public function __construct(
            private SiteFinder $siteFinder,
        ) {}

        public function getTemplateRootPaths(): array
        {
            return [];
        }

        public function getPartialRootPaths(): array
        {
            return [];
        }

        public function isCacheable(): bool
        {
            return false;
        }

        public static function getPriority(): int
        {
            return 25;
        }
    }

The class is picked up automatically because :php:`PathProvider` carries
:php:`#[AutoconfigureTag('handlebars.template_path_provider')]`. No extra
:file:`Services.yaml` entry is needed beyond standard autowiring.

..  _developer-corner-variable-provider:

VariableProvider
================

Implement the
:php:interface:`CPSIT\\Typo3Handlebars\\Renderer\\Variables\\VariableProvider`
interface to inject variables into every template rendering without repeating
them in TypoScript. Common uses include a site-wide locale string, feature
flags, or shared navigation data.

Providers are merged in ascending priority order; a higher-priority provider
can overwrite keys from a lower-priority one. The built-in
:php:`GlobalVariableProvider` uses priority 0.

..  php:namespace:: CPSIT\Typo3Handlebars\Renderer\Variables

..  php:interface:: VariableProvider

    ..  php:method:: get()

        Return the variables contributed by this provider.

        :returntype: array

    ..  php:method:: getPriority()

        Return the priority of this provider. Higher values win.

        :returntype: int

..  code-block:: php
    :caption: EXT:my_extension/Classes/Renderer/Variables/SiteVariableProvider.php

    namespace Vendor\Extension\Renderer\Variables;

    use CPSIT\Typo3Handlebars\Renderer\Variables\VariableProvider;
    use TYPO3\CMS\Core\Site\SiteFinder;

    final readonly class SiteVariableProvider implements VariableProvider
    {
        public function __construct(
            private SiteFinder $siteFinder,
        ) {}

        public function get(): array
        {
            return [
                'siteName' => $this->siteFinder->getSiteByPageId(0)->getIdentifier(),
            ];
        }

        public function offsetExists(mixed $offset): bool
        {
            return array_key_exists($offset, $this->get());
        }

        public function offsetGet(mixed $offset): mixed
        {
            return $this->get()[$offset] ?? null;
        }

        public function offsetSet(mixed $offset, mixed $value): never
        {
            throw new \LogicException('Variables are read-only.', 1781693633);
        }

        public function offsetUnset(mixed $offset): never
        {
            throw new \LogicException('Variables are read-only.', 1781693639);
        }

        public static function getPriority(): int
        {
            return 10;
        }
    }

Like :php:`PathProvider`, the class is picked up automatically via
:php:`#[AutoconfigureTag('handlebars.variable_provider')]` on the interface.
