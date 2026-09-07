<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace CPSIT\Typo3Handlebars\DataProcessing\Media;

use CuyZ\Valinor;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Frontend;

/**
 * ConfigurableProcessor
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 *
 * @template T of Configuration\Configuration
 */
abstract class ConfigurableProcessor implements MediaProcessor
{
    private ?Core\Cache\Frontend\FrontendInterface $cache = null;
    private ?Valinor\Mapper\TreeMapper $mapper = null;

    public function injectCache(
        #[DependencyInjection\Attribute\Autowire(expression: 'service("TYPO3\\\\CMS\\\\Core\\\\Cache\\\\CacheManager").getCache("handlebars_media")')]
        Core\Cache\Frontend\FrontendInterface $cache,
    ): void {
        $this->cache = $cache;
    }

    /**
     * @throws Valinor\Mapper\MappingError
     */
    public function process(
        Frontend\ContentObject\ContentObjectRenderer $contentObjectRenderer,
        Extbase\Domain\Model\File|Core\Resource\ResourceInterface|Extbase\Domain\Model\FileReference $resource,
        array $configuration = [],
    ): array {
        $this->mapper ??= $this->createMapper();

        return $this->processFile(
            $contentObjectRenderer,
            $resource,
            $this->mapper->map($this->getConfigurationClass(), $configuration),
        );
    }

    /**
     * @param T $configuration
     * @return array<string, mixed>
     */
    abstract protected function processFile(
        Frontend\ContentObject\ContentObjectRenderer $contentObjectRenderer,
        Extbase\Domain\Model\File|Core\Resource\ResourceInterface|Extbase\Domain\Model\FileReference $resource,
        Configuration\Configuration $configuration,
    ): array;

    /**
     * @return class-string<T>
     */
    abstract protected function getConfigurationClass(): string;

    protected function createMapper(): Valinor\Mapper\TreeMapper
    {
        $mapperBuilder = (new Valinor\MapperBuilder())
            ->allowCastingToBoolean()
            ->allowCastingToInteger()
            ->allowNonSequentialList()
        ;

        if (($cacheBackend = $this->cache?->getBackend()) instanceof Core\Cache\Backend\SimpleFileBackend) {
            $mapperBuilder = $mapperBuilder->withCache(new Valinor\Cache\FileSystemCache($cacheBackend->getCacheDirectory()));
            $mapperBuilder->warmupCacheFor($this->getConfigurationClass());
        }

        return $mapperBuilder->mapper();
    }
}
