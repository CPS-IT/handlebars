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

namespace Fr\Typo3Handlebars\Tests\Unit\Fixtures\Classes\Traits;

use Fr\Typo3Handlebars\Traits;
use TYPO3\CMS\Frontend;

/**
 * DummyContentObjectRendererAwareTraitClass
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 */
final class DummyContentObjectRendererAwareTraitClass
{
    use Traits\ContentObjectRendererAwareTrait;

    public function getContentObjectRenderer(): ?Frontend\ContentObject\ContentObjectRenderer
    {
        return $this->contentObjectRenderer;
    }

    public function testAssertContentObjectRendererIsAvailable(): void
    {
        $this->assertContentObjectRendererIsAvailable();
    }
}
