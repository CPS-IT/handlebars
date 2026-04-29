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

namespace CPSIT\Typo3Handlebars\Tests\Functional\Fixtures\Classes;

use CPSIT\Typo3Handlebars\Renderer;

/**
 * DummyRenderer
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 */
final class DummyRenderer implements Renderer\Renderer
{
    /**
     * @var (\Closure(Renderer\RenderingContext): string|null)|null
     */
    public ?\Closure $testClosure = null;
    public ?Renderer\RenderingContext $lastContext = null;

    public function renderTemplate(Renderer\RenderingContext $context): string
    {
        return $this->render($context) ?? $context->getTemplate();
    }

    public function renderPartial(Renderer\RenderingContext $context): string
    {
        return $this->render($context) ?? $context->getPartial();
    }

    private function render(Renderer\RenderingContext $context): ?string
    {
        $result = null;

        $this->lastContext = $context;

        if ($this->testClosure !== null) {
            $result = ($this->testClosure)($context);
        }

        return $result;
    }
}
