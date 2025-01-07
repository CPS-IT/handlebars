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
use Fr\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use Psr\Log;
use TYPO3\TestingFramework;

use function trim;

/**
 * HelperRegistryTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Helper\HelperRegistry::class)]
final class HelperRegistryTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Log\Test\TestLogger $logger;
    private Src\Renderer\Helper\HelperRegistry $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = new Log\Test\TestLogger();
        $this->subject = new Src\Renderer\Helper\HelperRegistry($this->logger);
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('addLogsCriticalErrorIfGivenHelperIsInvalidDataProvider')]
    public function addLogsCriticalErrorIfGivenHelperIsInvalid(mixed $function): void
    {
        $this->subject->add('foo', $function);

        self::assertTrue($this->logger->hasCriticalThatPasses(function ($logRecord) use ($function) {
            self::assertSame('Error while registering Handlebars helper "foo".', $logRecord['message']);
            self::assertSame('foo', $logRecord['context']['name']);
            self::assertSame($function, $logRecord['context']['function']);
            return true;
        }));
        self::assertSame([], $this->subject->getAll());
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('addRegistersHelperCorrectlyDataProvider')]
    public function addRegistersHelperCorrectly(mixed $function, callable $expectedCallable): void
    {
        $this->subject->add('foo', $function);

        $expected = $this->mapExpectedCallable($expectedCallable);

        self::assertEquals($expected, $this->subject->get('foo'));
    }

    #[Framework\Attributes\Test]
    public function addDecoratesHelperFunction(): void
    {
        $function = static fn(Src\Renderer\Helper\Context\HelperContext $context) => $context[0] . $context['foo'];

        $options = [
            'hash' => [
                'foo' => 'baz',
            ],
            'contexts' => [null],
            '_this' => [],
            'data' => [],
        ];

        $this->subject->add('foo', $function);

        self::assertSame('foobaz', $this->subject->get('foo')('foo', $options));
    }

    #[Framework\Attributes\Test]
    public function addOverridesAvailableHelper(): void
    {
        $this->subject->add('foo', 'trim');

        self::assertEquals($this->mapExpectedCallable(trim(...)), $this->subject->get('foo'));

        $this->subject->add('foo', 'strtolower');

        self::assertEquals($this->mapExpectedCallable(strtolower(...)), $this->subject->get('foo'));
    }

    #[Framework\Attributes\Test]
    public function getThrowsExceptionIfGivenHelperIsNotRegistered(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\HelperIsNotRegistered('foo'),
        );

        $this->subject->get('foo');
    }

    #[Framework\Attributes\Test]
    public function getReturnsRegisteredHelper(): void
    {
        $this->subject->add('foo', 'trim');

        self::assertEquals($this->mapExpectedCallable(trim(...)), $this->subject->get('foo'));
    }

    #[Framework\Attributes\Test]
    public function getAllReturnsRegisteredHelpers(): void
    {
        self::assertSame([], $this->subject->getAll());

        $this->subject->add('foo', 'strtolower');

        self::assertEquals(['foo' => $this->mapExpectedCallable(strtolower(...))], $this->subject->getAll());
    }

    #[Framework\Attributes\Test]
    public function hasReturnsTrueIfGivenHelperIsRegistered(): void
    {
        self::assertFalse($this->subject->has('foo'));

        $this->subject->add('foo', 'strtolower');

        self::assertTrue($this->subject->has('foo'));
    }

    /**
     * @return \Generator<string, array{mixed}>
     */
    public static function addLogsCriticalErrorIfGivenHelperIsInvalidDataProvider(): \Generator
    {
        yield 'null value' => [null];
        yield 'non-callable function as string' => ['foo_baz'];
        yield 'non-callable class method' => [Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper::class . '::foo'];
        yield 'non-callable class method in array syntax' => [[new Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper(), 'foo']];
        yield 'non-callable private class method' => [Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper::class . '::executeInternal'];
    }

    /**
     * @return \Generator<string, array{mixed, mixed}>
     */
    public static function addRegistersHelperCorrectlyDataProvider(): \Generator
    {
        yield 'callable as string' => [
            'trim',
            trim(...),
        ];
        yield 'invokable class as string' => [
            Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyInvokableHelper::class,
            new Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyInvokableHelper(),
        ];
        yield 'class implementing Helper interface as string' => [
            Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper::class,
            (new Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper())->render(...),
        ];
        yield 'callable as closure' => [
            static fn() => 'foo',
            static fn() => 'foo',
        ];
        yield 'callable as first class callable syntax' => [
            trim(...),
            trim(...),
        ];
        yield 'invokable class as object' => [
            new Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyInvokableHelper(),
            new Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyInvokableHelper(),
        ];
        yield 'class implementing Helper interface as object' => [
            new Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper(),
            (new Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper())->render(...),
        ];
        yield 'static class method as string' => [
            Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper::class . '::staticExecute',
            [Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper::class, 'staticExecute'],
        ];
        yield 'non-static class method as string' => [
            Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper::class . '::execute',
            [new Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper(), 'execute'],
        ];
        yield 'static class method as array' => [
            [Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper::class, 'staticExecute'],
            [Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper::class, 'staticExecute'],
        ];
        yield 'non-static class method as array' => [
            [Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper::class, 'execute'],
            [new Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper(), 'execute'],
        ];
        yield 'class method as initialized array' => [
            [new Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper(), 'execute'],
            [new Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper(), 'execute'],
        ];
    }

    /**
     * @return callable(\Fr\Typo3Handlebars\Renderer\Helper\Context\HelperContext): mixed
     */
    private function mapExpectedCallable(callable $expectedCallable): callable
    {
        return static function () use ($expectedCallable) {
            $arguments = \func_get_args();
            $context = Src\Renderer\Helper\Context\HelperContext::fromRuntimeCall($arguments);

            return $expectedCallable($context);
        };
    }
}
