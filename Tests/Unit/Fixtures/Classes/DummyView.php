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

namespace CPSIT\Typo3Handlebars\Tests\Unit\Fixtures\Classes;

use TYPO3\CMS\Core;

/**
 * DummyView
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 */
final class DummyView implements Core\View\ViewInterface
{
    /**
     * @var array<string, mixed>
     */
    public array $assignedVariables = [];
    public string $expectedTemplateResult = '';

    public function assign(string $key, mixed $value): self
    {
        $this->assignedVariables[$key] = $value;

        return $this;
    }

    /**
     * @param array<string, mixed> $values
     */
    public function assignMultiple(array $values): self
    {
        foreach ($values as $key => $value) {
            $this->assign($key, $value);
        }

        return $this;
    }

    public function render(string $templateFileName = ''): string
    {
        return $this->expectedTemplateResult;
    }
}
