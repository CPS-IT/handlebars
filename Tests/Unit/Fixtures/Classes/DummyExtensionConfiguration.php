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

namespace Fr\Typo3Handlebars\Tests\Unit\Fixtures\Classes;

use TYPO3\CMS\Core;

/**
 * DummyExtensionConfiguration
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 */
final class DummyExtensionConfiguration extends Core\Configuration\ExtensionConfiguration
{
    /**
     * @param array<string, bool> $activatedFeatures
     */
    public function __construct(
        private readonly array $activatedFeatures,
    ) {}

    /**
     * @throws Core\Exception
     */
    public function get(string $extension, string $path = ''): bool
    {
        [, $featureName] = explode('/', $path);

        if (!isset($this->activatedFeatures[$featureName])) {
            throw new Core\Exception('dummy exception');
        }

        return $this->activatedFeatures[$featureName];
    }
}
