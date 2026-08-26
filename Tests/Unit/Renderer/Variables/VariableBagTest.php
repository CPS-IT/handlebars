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

namespace CPSIT\Typo3Handlebars\Tests\Unit\Renderer\Variables;

use CPSIT\Typo3Handlebars as Src;
use CPSIT\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * VariableBagTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Variables\VariableBag::class)]
final class VariableBagTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Tests\Unit\Fixtures\Classes\Renderer\Variables\DummyVariableProvider $variableProvider;
    private Src\Renderer\Variables\VariableBag $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->variableProvider = new Tests\Unit\Fixtures\Classes\Renderer\Variables\DummyVariableProvider([
            'baz' => 'boo',
        ]);
        $this->subject = new Src\Renderer\Variables\VariableBag([
            $this->variableProvider,
            new Src\Renderer\Variables\GlobalVariableProvider([
                'foo' => 'boo',
                'baz' => 'foo',
            ]),
            new Src\Renderer\Variables\GlobalVariableProvider([
                'foo' => 'baz',
            ]),
        ]);
    }

    #[Framework\Attributes\Test]
    public function getReturnsMergedVariablesFromProviders(): void
    {
        $expected = [
            'foo' => 'boo',
            'baz' => 'boo',
        ];

        self::assertSame($expected, $this->subject->get());
    }

    #[Framework\Attributes\Test]
    public function getRespectsNonCacheableStateOfProviders(): void
    {
        $this->variableProvider->cacheable = false;

        self::assertSame('boo', $this->subject->get()['baz']);

        $this->variableProvider->variables = [];

        self::assertSame('foo', $this->subject->get()['baz']);
    }

    #[Framework\Attributes\Test]
    public function objectCanBeAccessedAsReadOnlyArray(): void
    {
        // offsetExists
        self::assertTrue(isset($this->subject['foo']));
        self::assertTrue(isset($this->subject['baz']));
        self::assertFalse(isset($this->subject['boo']));

        // offsetGet
        self::assertSame('boo', $this->subject['foo']);
        self::assertSame('boo', $this->subject['baz']);
        self::assertNull($this->subject['boo']);
    }

    #[Framework\Attributes\Test]
    public function offsetSetThrowsLogicException(): void
    {
        $this->expectExceptionObject(
            new \LogicException('Variables cannot be modified.', 1736274871),
        );

        $this->subject['boo'] = 'baz';
    }

    #[Framework\Attributes\Test]
    public function offsetUnsetThrowsLogicException(): void
    {
        $this->expectExceptionObject(
            new \LogicException('Variables cannot be modified.', 1736274873),
        );

        unset($this->subject['foo']);
    }
}
