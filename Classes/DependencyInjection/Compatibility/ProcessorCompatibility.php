<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2021 Elias Häußler <e.haeussler@familie-redlich.de>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace Fr\Typo3Handlebars\DependencyInjection\Compatibility;

use Fr\Typo3Handlebars\Exception\UnsupportedTypeException;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * ProcessorCompatibility
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 */
final class ProcessorCompatibility
{
    /**
     * @var string
     */
    private $serviceId;

    /**
     * @var array<string, mixed>
     */
    private $tagAttributes;

    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @param array<string, mixed> $tagAttributes
     */
    public function __construct(string $serviceId, array $tagAttributes, ContainerBuilder $container)
    {
        $this->serviceId = $serviceId;
        $this->tagAttributes = $tagAttributes;
        $this->container = $container;

        $this->validate();
    }

    public function provideCompatibility(): void
    {
        $configuration = $this->tagAttributes;
        $type = $configuration['type'];
        unset($configuration['type']);

        $compatibility = $this->buildLayerForType($type);
        $compatibility->provide($this->serviceId, $configuration);
    }

    /**
     * @throws UnsupportedTypeException
     */
    private function buildLayerForType(string $type): CompatibilityLayerInterface
    {
        switch ($type) {
            case ExtbaseControllerCompatibilityLayer::TYPE:
                return new ExtbaseControllerCompatibilityLayer($this->container);

            default:
                throw UnsupportedTypeException::create($type);
        }
    }

    private function validate(): void
    {
        if (!isset($this->tagAttributes['type'])) {
            throw new \LogicException(
                'Processor compatibility layers must have a type configured!',
                1632813645
            );
        }
    }
}
