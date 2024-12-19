<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2020 Elias Häußler <e.haeussler@familie-redlich.de>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace Fr\Typo3Handlebars\Tests\Unit\Renderer\Helper;

use Fr\Typo3Handlebars as Src;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * VarDumpHelperTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Helper\VarDumpHelper::class)]
final class VarDumpHelperTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Src\Renderer\Helper\VarDumpHelper $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\Renderer\Helper\VarDumpHelper();
    }

    #[Framework\Attributes\Test]
    public function evaluateReturnsDumpedContext(): void
    {
        Core\Utility\DebugUtility::useAnsiColor(false);

        $renderingContext = [
            'foo' => 'baz',
        ];
        $data = [];
        $stack = [];

        $context = new Src\Renderer\Helper\Context\HelperContext(
            [],
            [],
            new Src\Renderer\Helper\Context\RenderingContextStack($stack),
            $renderingContext,
            $data,
        );

        $expected = <<<EOF
Debug
array(1 item)
   foo => "baz" (3 chars)
EOF;

        self::assertSame($expected, $this->subject->render($context));

        Core\Utility\DebugUtility::useAnsiColor(true);
    }
}
