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

namespace Fr\Typo3Handlebars\Traits;

use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * ContentObjectRendererAwareTrait
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
trait ContentObjectRendererAwareTrait
{
    protected ?ContentObjectRenderer $contentObjectRenderer = null;

    public function setContentObjectRenderer(ContentObjectRenderer $contentObjectRenderer): void
    {
        $this->contentObjectRenderer = $contentObjectRenderer;
    }

    /**
     * @phpstan-assert ContentObjectRenderer $this->contentObjectRenderer
     */
    protected function assertContentObjectRendererIsAvailable(): void
    {
        if (!($this->contentObjectRenderer instanceof ContentObjectRenderer)) {
            throw new \InvalidArgumentException(
                'Content object renderer is not available.',
                1615813615
            );
        }
    }
}
