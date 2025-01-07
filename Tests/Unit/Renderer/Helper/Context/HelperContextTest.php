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

namespace Fr\Typo3Handlebars\Tests\Unit\Renderer\Helper\Context;

use Fr\Typo3Handlebars as Src;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * HelperContextTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Helper\Context\HelperContext::class)]
final class HelperContextTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Src\Renderer\Helper\Context\HelperContext $subject;

    /**
     * @var list<array<string, string>>
     */
    private array $stack = [
        ['foo' => 'baz'],
        ['baz' => 'foo'],
    ];

    /**
     * @var array<string, string>
     */
    private array $renderingContext = [
        'foo' => 'baz',
    ];

    /**
     * @var array<'root'|int, array<string, string>>
     */
    private array $data = [
        'root' => [
            'foo' => 'baz',
        ],
        [
            'foo' => 'baz',
        ],
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\Renderer\Helper\Context\HelperContext(
            [
                'foo',
                'baz',
            ],
            [
                'foo' => 'baz',
            ],
            new Src\Renderer\Helper\Context\RenderingContextStack($this->stack),
            $this->renderingContext,
            $this->data,
            static fn() => 'foo',
            static fn() => 'baz',
        );
    }

    #[Framework\Attributes\Test]
    public function fromRuntimeCallReturnsConstructedContext(): void
    {
        $options = $this->getRuntimeOptions();

        $actual = Src\Renderer\Helper\Context\HelperContext::fromRuntimeCall($options);

        self::assertEquals($this->subject, $actual);
    }

    #[Framework\Attributes\Test]
    public function fromRuntimeCallPassesContextStackAsReference(): void
    {
        $options = $this->getRuntimeOptions();

        $actual = Src\Renderer\Helper\Context\HelperContext::fromRuntimeCall($options);

        $this->stack[] = ['boo' => 'faz'];

        self::assertSame(
            [
                ['boo' => 'faz'],
                ['baz' => 'foo'],
                ['foo' => 'baz'],
            ],
            \iterator_to_array($actual->contextStack),
        );
    }

    #[Framework\Attributes\Test]
    public function fromRuntimeCallPassesRenderingContextAsReference(): void
    {
        $options = $this->getRuntimeOptions();

        $actual = Src\Renderer\Helper\Context\HelperContext::fromRuntimeCall($options);

        $this->renderingContext['baz'] = 'foo';

        self::assertSame(
            [
                'foo' => 'baz',
                'baz' => 'foo',
            ],
            $actual->renderingContext,
        );
    }

    #[Framework\Attributes\Test]
    public function fromRuntimeCallPassesDataAsReference(): void
    {
        $options = $this->getRuntimeOptions();

        $actual = Src\Renderer\Helper\Context\HelperContext::fromRuntimeCall($options);

        $this->data[] = ['baz' => 'foo'];

        self::assertSame(
            [
                'root' => [
                    'foo' => 'baz',
                ],
                [
                    'foo' => 'baz',
                ],
                [
                    'baz' => 'foo',
                ],
            ],
            $actual->data,
        );
    }

    #[Framework\Attributes\Test]
    public function isBlockHelperReturnsTrueIfChildrenClosureExists(): void
    {
        self::assertTrue($this->subject->isBlockHelper());

        $options = $this->getRuntimeOptions(false);
        $subject = Src\Renderer\Helper\Context\HelperContext::fromRuntimeCall($options);

        self::assertFalse($subject->isBlockHelper());
    }

    #[Framework\Attributes\Test]
    public function renderChildrenReturnsNullIfNoChildrenClosureExists(): void
    {
        $options = $this->getRuntimeOptions(false);
        $subject = Src\Renderer\Helper\Context\HelperContext::fromRuntimeCall($options);

        self::assertNull($subject->renderChildren());
    }

    #[Framework\Attributes\Test]
    public function renderChildrenInvokesChildrenClosureWithGivenArguments(): void
    {
        $options = $this->getRuntimeOptions();
        $subject = Src\Renderer\Helper\Context\HelperContext::fromRuntimeCall($options);

        self::assertSame('fn: foo', $subject->renderChildren('foo'));
    }

    #[Framework\Attributes\Test]
    public function renderInverseReturnsNullIfNoInverseClosureExists(): void
    {
        $options = $this->getRuntimeOptions(true, false);
        $subject = Src\Renderer\Helper\Context\HelperContext::fromRuntimeCall($options);

        self::assertNull($subject->renderInverse());
    }

    #[Framework\Attributes\Test]
    public function renderInverseInvokesInverseClosureWithGivenArguments(): void
    {
        $options = $this->getRuntimeOptions();
        $subject = Src\Renderer\Helper\Context\HelperContext::fromRuntimeCall($options);

        self::assertSame('inverse: foo', $subject->renderInverse('foo'));
    }

    #[Framework\Attributes\Test]
    public function objectCanBeAccessedAsReadOnlyArray(): void
    {
        // offsetExists
        self::assertTrue(isset($this->subject[0]));
        self::assertTrue(isset($this->subject[1]));
        self::assertFalse(isset($this->subject[2]));
        self::assertTrue(isset($this->subject['foo']));
        self::assertFalse(isset($this->subject['baz']));

        // offsetGet
        self::assertSame('foo', $this->subject[0]);
        self::assertSame('baz', $this->subject[1]);
        self::assertSame('baz', $this->subject['foo']);
    }

    #[Framework\Attributes\Test]
    public function offsetGetThrowsExceptionIfGivenArgumentDoesNotExist(): void
    {
        $this->expectExceptionObject(
            new \OutOfBoundsException('Argument "99" does not exist.', 1736235839),
        );

        $x = $this->subject[99];
    }

    #[Framework\Attributes\Test]
    public function offsetGetThrowsExceptionIfGivenHashDoesNotExist(): void
    {
        $this->expectExceptionObject(
            new \OutOfBoundsException('Hash "missing" does not exist.', 1736235851),
        );

        $x = $this->subject['missing'];
    }

    #[Framework\Attributes\Test]
    public function offsetSetThrowsLogicException(): void
    {
        $this->expectExceptionObject(
            new \LogicException('Helper context is locked and cannot be modified.', 1734434746),
        );

        $this->subject['baz'] = 'foo';
    }

    #[Framework\Attributes\Test]
    public function offsetUnsetThrowsLogicException(): void
    {
        $this->expectExceptionObject(
            new \LogicException('Helper context is locked and cannot be modified.', 1734434780),
        );

        unset($this->subject['foo']);
    }

    /**
     * @return array{
     *     string,
     *     string,
     *     array{
     *         hash: array<string, string>,
     *         contexts: list<array<string, string>>,
     *         _this: array<string, string>,
     *         data: array<'root'|int, array<string, string>>,
     *         fn?: callable(string): string,
     *         inverse?: callable(string): string,
     *     },
     * }
     */
    private function getRuntimeOptions(bool $includeChildrenClosure = true, bool $includeInverseClosure = true): array
    {
        $options = [
            'foo',
            'baz',
            [
                'hash' => [
                    'foo' => 'baz',
                ],
                'contexts' => &$this->stack,
                '_this' => &$this->renderingContext,
                'data' => &$this->data,
            ],
        ];

        if ($includeChildrenClosure) {
            $options[2]['fn'] = static fn(string $input) => 'fn: ' . $input;
        }

        if ($includeInverseClosure) {
            $options[2]['inverse'] = static fn(string $input) => 'inverse: ' . $input;
        }

        return $options;
    }
}
