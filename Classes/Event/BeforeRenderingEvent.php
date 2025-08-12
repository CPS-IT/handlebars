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
use CPSIT\Typo3Handlebars\View;

/**
 * BeforeRenderingEvent
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class BeforeRenderingEvent
{
    /**
     * @param array<string|int, mixed> $variables
     */
    public function __construct(
        private readonly View\HandlebarsView $view,
        private array $variables,
        private readonly Renderer\Renderer $renderer,
    ) {}

    public function getView(): View\HandlebarsView
    {
        return $this->view;
    }

    /**
     * @return array<string|int, mixed>
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * @param array<string|int, mixed> $variables
     */
    public function setVariables(array $variables): self
    {
        $this->variables = $variables;
        return $this;
    }

    public function addVariable(string $name, mixed $value): self
    {
        $this->variables[$name] = $value;
        return $this;
    }

    public function removeVariable(string $name): self
    {
        unset($this->variables[$name]);
        return $this;
    }

    public function getRenderer(): Renderer\Renderer
    {
        return $this->renderer;
    }
}
