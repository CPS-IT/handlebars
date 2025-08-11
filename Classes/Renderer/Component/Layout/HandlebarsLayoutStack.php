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

namespace CPSIT\Typo3Handlebars\Renderer\Component\Layout;

use TYPO3\CMS\Core;

/**
 * HandlebarsLayoutStack
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @implements \IteratorAggregate<HandlebarsLayout>
 */
final class HandlebarsLayoutStack implements Core\SingletonInterface, \IteratorAggregate
{
    /**
     * @var HandlebarsLayout[]
     */
    private array $stack = [];

    public function push(HandlebarsLayout $layout): self
    {
        $this->stack[] = $layout;

        return $this;
    }

    public function pop(): ?HandlebarsLayout
    {
        return array_pop($this->stack);
    }

    public function first(): ?HandlebarsLayout
    {
        $first = reset($this->stack);

        if ($first === false) {
            return null;
        }

        return $first;
    }

    public function last(): ?HandlebarsLayout
    {
        $last = end($this->stack);

        if ($last === false) {
            return null;
        }

        return $last;
    }

    /**
     * @return HandlebarsLayout[]
     */
    public function all(): array
    {
        return $this->stack;
    }

    public function reset(): void
    {
        $this->stack = [];
    }

    /**
     * @phpstan-assert-if-false HandlebarsLayout $this->pop()
     * @phpstan-assert-if-false HandlebarsLayout $this->first()
     * @phpstan-assert-if-false HandlebarsLayout $this->last()
     */
    public function isEmpty(): bool
    {
        return $this->stack === [];
    }

    /**
     * @return \Generator<HandlebarsLayout>
     */
    public function getIterator(): \Generator
    {
        yield from array_reverse($this->stack);
    }
}
