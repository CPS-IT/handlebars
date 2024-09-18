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

use Fr\Typo3Handlebars\Renderer\Helper\VarDumpHelper;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * VarDumpHelperTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class VarDumpHelperTest extends UnitTestCase
{
    /**
     * @test
     */
    public function evaluateReturnsDumpedContext(): void
    {
        DebugUtility::usePlainTextOutput(true);
        DebugUtility::useAnsiColor(false);

        $context = [
            '_this' => [
                'foo' => 'baz',
            ],
        ];

        $expected = <<<EOF
Debug
array(1 item)
   _this => array(1 item)
      foo => "baz" (3 chars)
EOF;

        self::assertSame($expected, VarDumpHelper::evaluate($context));
    }
}
