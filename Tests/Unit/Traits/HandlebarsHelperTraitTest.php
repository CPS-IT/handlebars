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
 * HandlebarsHelperTraitTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Traits\HandlebarsHelperTrait::class)]
final class HandlebarsHelperTraitTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Log\Test\TestLogger $logger;
    private Tests\Unit\Fixtures\Classes\Traits\DummyHandlebarsHelperTraitClass $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = new Log\Test\TestLogger();
        $this->subject = new Tests\Unit\Fixtures\Classes\Traits\DummyHandlebarsHelperTraitClass($this->logger);
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('registerHelperLogsCriticalErrorIfGivenHelperIsInvalidDataProvider')]
    public function registerHelperLogsCriticalErrorIfGivenHelperIsInvalid(mixed $function): void
    {
        $this->subject->registerHelper('foo', $function);
        self::assertTrue($this->logger->hasCriticalThatPasses(function ($logRecord) use ($function) {
            self::assertSame('Error while registering Handlebars helper "foo".', $logRecord['message']);
            self::assertSame('foo', $logRecord['context']['name']);
            self::assertSame($function, $logRecord['context']['function']);
            return true;
        }));
        self::assertSame([], $this->subject->getHelpers());
    }

    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('registerHelperRegistersHelperCorrectlyDataProvider')]
    public function registerHelperRegistersHelperCorrectly(mixed $function, callable $expectedCallable): void
    {
        $this->subject->registerHelper('foo', $function);

        $expected = $this->mapExpectedCallable($expectedCallable);

        self::assertEquals(['foo' => $expected], $this->subject->getHelpers());
    }

    #[Framework\Attributes\Test]
    public function registerHelperOverridesAvailableHelper(): void
    {
        $this->subject->registerHelper('foo', 'trim');
        self::assertEquals(['foo' => $this->mapExpectedCallable(trim(...))], $this->subject->getHelpers());

        $this->subject->registerHelper('foo', 'strtolower');
        self::assertEquals(['foo' => $this->mapExpectedCallable(strtolower(...))], $this->subject->getHelpers());
    }

    #[Framework\Attributes\Test]
    public function getHelpersReturnsRegisteredHelpers(): void
    {
        self::assertSame([], $this->subject->getHelpers());

        $this->subject->registerHelper('foo', 'strtolower');
        self::assertEquals(['foo' => $this->mapExpectedCallable(strtolower(...))], $this->subject->getHelpers());
    }

    /**
     * @return \Generator<string, array{mixed}>
     */
    public static function registerHelperLogsCriticalErrorIfGivenHelperIsInvalidDataProvider(): \Generator
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
    public static function registerHelperRegistersHelperCorrectlyDataProvider(): \Generator
    {
        yield 'callable function as string' => [
            'trim',
            trim(...),
        ];
        yield 'invokable class as string' => [
            Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper::class,
            new Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper(),
        ];
        yield 'invokable class as object' => [
            new Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper(),
            new Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper(),
        ];
        yield 'callable static class method' => [
            Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper::class . '::staticExecute',
            [Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper::class, 'staticExecute'],
        ];
        yield 'callable non-static class method' => [
            Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper::class . '::execute',
            [new Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper(), 'execute'],
        ];
        yield 'callable static class method in array syntax' => [
            [Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper::class, 'staticExecute'],
            [Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper::class, 'staticExecute'],
        ];
        yield 'callable non-static class method in array syntax' => [
            [Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper::class, 'execute'],
            [new Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper(), 'execute'],
        ];
        yield 'callable non-static class method in initialized array syntax' => [
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
