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

namespace Fr\Typo3Handlebars\Generator\Writer;

use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * YamlWriter
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 */
class YamlWriter
{
    /**
     * @param array<mixed, mixed> $content
     */
    public function write(string $filename, array $content): bool
    {
        return GeneralUtility::writeFile($filename, $this->fill($filename, $content));
    }

    /**
     * @param array<mixed, mixed> $content
     */
    public function fill(string $filename, array $content, bool $returnOnlyChangedContent = false): string
    {
        if (!$returnOnlyChangedContent) {
            $yaml = Yaml::parseFile($filename);
            ArrayUtility::mergeRecursiveWithOverrule($yaml, $content);
        } else {
            $yaml = $content;
        }

        return Yaml::dump($yaml, 99, 2);
    }

    public function writeDefaultServicesYaml(string $filename, string $vendorNamespace): bool
    {
        if (file_exists($filename)) {
            throw new \RuntimeException(
                sprintf('File "%s" already exists and should therefore not be overridden.', $filename),
                1622139986
            );
        }

        $defaultConfiguration = [
            'services' => [
                '_defaults' => [
                    'autowire' => true,
                    'autoconfigure' => true,
                    'public' => false,
                ],
                rtrim($vendorNamespace, '\\') . '\\' => [
                    'resource' => '../Classes/*',
                ],
            ],
        ];
        $yaml = Yaml::dump($defaultConfiguration);

        return GeneralUtility::writeFile($filename, $yaml);
    }
}
