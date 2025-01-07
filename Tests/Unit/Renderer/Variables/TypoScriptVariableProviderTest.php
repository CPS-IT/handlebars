<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2025 Elias Häußler <e.haeussler@familie-redlich.de>
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

namespace Fr\Typo3Handlebars\Tests\Unit\Renderer\Variables;

use Fr\Typo3Handlebars as Src;
use Fr\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * TypoScriptVariableProviderTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Variables\TypoScriptVariableProvider::class)]
final class TypoScriptVariableProviderTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Tests\Unit\Fixtures\Classes\DummyConfigurationManager $configurationManager;
    private Src\Renderer\Variables\TypoScriptVariableProvider $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->configurationManager = new Tests\Unit\Fixtures\Classes\DummyConfigurationManager();
        $this->configurationManager->configuration = [
            'variables' => [
                'foo' => 'baz',
            ],
        ];

        $this->subject = new Src\Renderer\Variables\TypoScriptVariableProvider($this->configurationManager);
    }

    #[Framework\Attributes\Test]
    public function getReturnsVariablesFetchedViaConfigurationManager(): void
    {
        self::assertSame(['foo' => 'baz'], $this->subject->get());
    }

    #[Framework\Attributes\Test]
    public function getCachesFetchedVariables(): void
    {
        self::assertSame(['foo' => 'baz'], $this->subject->get());

        $this->configurationManager->configuration = [];

        self::assertSame(['foo' => 'baz'], $this->subject->get());
    }

    #[Framework\Attributes\Test]
    public function objectCanBeAccessedAsReadOnlyArray(): void
    {
        // offsetExists
        self::assertTrue(isset($this->subject['foo']));
        self::assertFalse(isset($this->subject['baz']));

        // offsetGet
        self::assertSame('baz', $this->subject['foo']);
        self::assertNull($this->subject['baz']);
    }

    #[Framework\Attributes\Test]
    public function offsetSetThrowsLogicException(): void
    {
        $this->expectExceptionObject(
            new \LogicException('Variables cannot be modified.', 1736274326),
        );

        $this->subject['baz'] = 'foo';
    }

    #[Framework\Attributes\Test]
    public function offsetUnsetThrowsLogicException(): void
    {
        $this->expectExceptionObject(
            new \LogicException('Variables cannot be modified.', 1736274336),
        );

        unset($this->subject['foo']);
    }
}
