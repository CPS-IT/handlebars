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

namespace CPSIT\Typo3Handlebars\Tests\Unit\Fixtures\Classes\Renderer\Variables;

use CPSIT\Typo3Handlebars\Renderer;

/**
 * DummyVariableProvider
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 *
 * @extends \ArrayObject<string, mixed>
 */
final class DummyVariableProvider extends \ArrayObject implements Renderer\Variables\VariableProvider
{
    /**
     * @param array<string, mixed> $variables
     */
    public function __construct(
        public array $variables,
        public bool $cacheable = true,
    ) {
        parent::__construct($this->variables);
    }

    public function get(): array
    {
        return $this->variables;
    }

    public function isCacheable(): bool
    {
        return $this->cacheable;
    }

    public static function getPriority(): int
    {
        return 100;
    }
}
