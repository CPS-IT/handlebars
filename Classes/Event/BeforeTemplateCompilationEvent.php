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

namespace CPSIT\Typo3Handlebars\Event;

use CPSIT\Typo3Handlebars\Renderer;

/**
 * BeforeTemplateCompilationEvent
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final readonly class BeforeTemplateCompilationEvent
{
    public function __construct(
        private Renderer\RenderingContext $context,
        private Renderer\Renderer $renderer,
    ) {}

    public function getContext(): Renderer\RenderingContext
    {
        return $this->context;
    }

    public function getRenderer(): Renderer\Renderer
    {
        return $this->renderer;
    }
}
