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

namespace Fr\Typo3Handlebars\Generator\Resolver;

use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ClassResolver
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 */
class ClassResolver
{
    /**
     * @var PackageManager
     */
    protected $packageManager;

    public function __construct(PackageManager $packageManager)
    {
        $this->packageManager = $packageManager;
    }

    /**
     * @return array{namespace: string, className: string}
     */
    public function buildClassParts(
        string $extensionKey,
        string $baseName,
        string $namespacePath = null,
        string $classNameSuffix = null
    ): array {
        $vendorNamespace = $this->resolveVendorNamespace($extensionKey);
        $className = $this->sanitizeNamespacePart($baseName);

        if (!empty($classNameSuffix)) {
            $className .= $this->sanitizeNamespacePart($classNameSuffix);
        }

        $namespace = implode('\\', array_map(function (string $namespaceComponent) {
            return trim($namespaceComponent, '\\');
        }, [
            $vendorNamespace,
            $this->sanitizeNamespacePart($namespacePath ?? ''),
        ]));

        return [
            'namespace' => $namespace,
            'className' => $className,
        ];
    }

    public function resolveVendorNamespace(string $extensionKey): string
    {
        $package = $this->packageManager->getPackage($extensionKey);
        $autoloadInformation = $package->getValueFromComposerManifest('autoload');

        if ($autoloadInformation === null || !isset($autoloadInformation->{'psr-4'})) {
            throw new \InvalidArgumentException(
                sprintf('Autoload information for extension with key "%s" is missing.', $extensionKey),
                1622135990
            );
        }

        foreach ((array)$autoloadInformation->{'psr-4'} as $vendorNamespace => $rootPath) {
            if (rtrim($rootPath, '/') === 'Classes') {
                return rtrim($vendorNamespace, '\\') . '\\';
            }
        }

        throw new \RuntimeException(
            sprintf('Unable to determine autoload information for extension with key "%s".', $extensionKey),
            1622136229
        );
    }

    public function sanitizeNamespacePart(string $namespacePart): string
    {
        return trim(
            (string)preg_replace(
                '/[^\w\\\]/',
                '',
                ucwords(str_replace('\\', '\\ ', $namespacePart))
            ),
            '\\'
        );
    }

    public function locateClass(string $extensionKey, string $className): string
    {
        $vendorNamespace = $this->resolveVendorNamespace($extensionKey);

        if (!str_starts_with($className, $vendorNamespace)) {
            throw new \InvalidArgumentException(
                sprintf('The given class name "%s" is not part of the extension with key "%s"!', $className, $extensionKey),
                1622137836
            );
        }

        $absFilename = sprintf(
            'EXT:%s/Classes/%s.php',
            $extensionKey,
            str_replace('\\', DIRECTORY_SEPARATOR, rtrim(substr($className, \strlen($vendorNamespace)), '\\'))
        );
        $filename = GeneralUtility::getFileAbsFileName($absFilename);

        if (empty($filename)) {
            throw new \RuntimeException(
                sprintf('Unable to locate class "%s" (tried to resolve "%s").', $className, $filename),
                1622138016
            );
        }

        return $filename;
    }
}
