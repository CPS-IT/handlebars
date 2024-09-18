<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2020 Elias Häußler <e.haeussler@familie-redlich.de>
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

namespace Fr\Typo3Handlebars\DependencyInjection;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * ProcessingBridge
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 */
final class ProcessingBridge
{
    private const PROCESSOR_CLASSNAME_PATTERN = '#(?P<vendorName>.+)\\\\DataProcessing\\\\(?P<baseProcessorName>.+?(?=Data)?)(Data)?Processor$#';

    private string $vendorName = '';
    private string $baseProcessorName = '';

    public function __construct(
        private readonly string $id,
        private readonly Definition $definition,
    ) {
        [$this->vendorName, $this->baseProcessorName] = $this->inspectProcessor();
    }

    public function getPresenter(): Reference
    {
        $serviceParts = [
            $this->vendorName,
            'Presenter',
            $this->baseProcessorName . 'Presenter',
        ];
        $providerService = implode('\\', $serviceParts);
        return new Reference($providerService);
    }

    public function getProvider(): Reference
    {
        $serviceParts = [
            $this->vendorName,
            'Data',
            $this->baseProcessorName . 'Provider',
        ];
        $providerService = implode('\\', $serviceParts);
        return new Reference($providerService);
    }

    public function hasMethodCall(string $method): bool
    {
        return $this->definition->hasMethodCall($method);
    }

    /**
     * @return array{string, string}
     */
    private function inspectProcessor(): array
    {
        // Throw exception if given data processor does not match expected class scheme
        if (!preg_match(self::PROCESSOR_CLASSNAME_PATTERN, $this->id, $matches)) {
            throw new \UnexpectedValueException(
                'Received unexpected data processor service name: ' . $this->id,
                1606326944
            );
        }

        return [
            $matches['vendorName'],
            $matches['baseProcessorName'],
        ];
    }
}
