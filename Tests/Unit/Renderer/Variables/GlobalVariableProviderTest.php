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

namespace Fr\Typo3Handlebars\Tests\Unit\Renderer\Variables;

use Fr\Typo3Handlebars as Src;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * GlobalVariableProviderTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Variables\GlobalVariableProvider::class)]
final class GlobalVariableProviderTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Src\Renderer\Variables\GlobalVariableProvider $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\Renderer\Variables\GlobalVariableProvider([
            'foo' => 'baz',
        ]);
    }

    #[Framework\Attributes\Test]
    public function getReturnsVariables(): void
    {
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
            new \LogicException('Variables cannot be modified.', 1736274549),
        );

        $this->subject['baz'] = 'foo';
    }

    #[Framework\Attributes\Test]
    public function offsetUnsetThrowsLogicException(): void
    {
        $this->expectExceptionObject(
            new \LogicException('Variables cannot be modified.', 1736274551),
        );

        unset($this->subject['foo']);
    }
}
