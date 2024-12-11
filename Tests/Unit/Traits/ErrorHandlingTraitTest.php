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

namespace Fr\Typo3Handlebars\Tests\Unit\Traits;

use Fr\Typo3Handlebars as Src;
use Fr\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use Psr\Log;
use TYPO3\TestingFramework;

/**
 * ErrorHandlingTraitTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Traits\ErrorHandlingTrait::class)]
final class ErrorHandlingTraitTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Log\Test\TestLogger $logger;
    private Tests\Unit\Fixtures\Classes\Traits\DummyErrorHandlingTraitClass $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = new Log\Test\TestLogger();
        $this->subject = new Tests\Unit\Fixtures\Classes\Traits\DummyErrorHandlingTraitClass($this->logger);
    }

    #[Framework\Attributes\Test]
    public function handleErrorLogsLogsCriticalError(): void
    {
        $exception = new \Exception();

        $this->subject->doHandleError($exception);
        self::assertTrue($this->logger->hasCriticalThatPasses(function ($logRecord) use ($exception) {
            $expectedMessage = 'Data processing for ' . $this->subject::class . ' failed.';
            self::assertSame($expectedMessage, $logRecord['message']);
            self::assertSame($exception, $logRecord['context']['exception']);
            return true;
        }));
    }
}
