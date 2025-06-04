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

use TYPO3Fluid\Fluid;

/**
 * DummyView
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 */
final readonly class DummyView implements Fluid\View\ViewInterface
{
    public function assign(string $key, mixed $value): self
    {
        // Intentionally left blank.

        return $this;
    }

    /**
     * @param array<string, mixed> $values
     */
    public function assignMultiple(array $values): self
    {
        // Intentionally left blank.

        return $this;
    }

    public function render(): string
    {
        return '';
    }

    /**
     * @param array<string, mixed> $variables
     */
    public function renderSection($sectionName, array $variables = [], $ignoreUnknown = false): string
    {
        return '';
    }

    /**
     * @param array<string, mixed> $variables
     */
    public function renderPartial($partialName, $sectionName, array $variables, $ignoreUnknown = false): string
    {
        return '';
    }
}
