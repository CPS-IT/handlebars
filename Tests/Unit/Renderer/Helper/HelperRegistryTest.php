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

namespace CPSIT\Typo3Handlebars\Tests\Unit\Renderer\Helper;

use CPSIT\Typo3Handlebars as Src;
use CPSIT\Typo3Handlebars\Tests;
use DevTheorem\Handlebars;
use EliasHaeussler\DeepClosureComparator;
use PHPUnit\Framework;
use Psr\Log;
use TYPO3\TestingFramework;

/**
 * HelperRegistryTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 *
 * @phpstan-type ValidHelperFunction array{object, string}|array{class-string, string}|callable|string|Src\Renderer\Helper\Helper
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

    /**
     * @param array{object, string}|string $function
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('addLogsCriticalErrorIfGivenHelperIsInvalidDataProvider')]
    public function addLogsCriticalErrorIfGivenHelperIsInvalid(array|string $function): void
    {
        $this->subject->add('foo', $function);

        self::assertTrue($this->logger->hasCriticalThatPasses(function (array $logRecord) use ($function) {
            self::assertSame('Error while registering Handlebars helper "foo".', $logRecord['message']);
            self::assertIsArray($logRecord['context']);
            self::assertSame('foo', $logRecord['context']['name']);
            self::assertSame($function, $logRecord['context']['function']);
            return true;
        }));
        self::assertSame([], $this->subject->getAll());
    }

    /**
     * @param ValidHelperFunction $function
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('addRegistersHelperCorrectlyDataProvider')]
    public function addRegistersHelperCorrectly(mixed $function, callable $expectedCallable): void
    {
        $this->subject->add('foo', $function);

        $expected = $this->mapExpectedCallable($expectedCallable);

        DeepClosureComparator\DeepClosureAssert::assertEquals($expected, $this->subject->get('foo'));
    }

    #[Framework\Attributes\DataProvider('addDecoratesHelperFunctionDataProvider')]
    #[Framework\Attributes\Test]
    public function addDecoratesHelperFunction(callable $function, string $expected): void
    {
        $renderingContext = new Src\Renderer\RenderingContext();
        $renderingContext->assign('baz', 'foo');

        $scope = [];
        $data = [
            'renderingContext' => $renderingContext,
        ];
        $options = new Handlebars\HelperOptions(
            $scope,
            $data,
            new Handlebars\RuntimeContext(),
            'foo',
            [
                'foo' => 'baz',
            ],
            0,
            static fn() => '',
            static fn() => '',
        );

        $this->subject->add('foo', $function);

        self::assertSame($expected, $this->subject->get('foo')('foo', $options));
    }

    #[Framework\Attributes\Test]
    public function addOverridesAvailableHelper(): void
    {
        $this->subject->add('foo', 'trim');

        DeepClosureComparator\DeepClosureAssert::assertEquals(
            $this->mapExpectedCallable('trim'),
            $this->subject->get('foo'),
        );

        $this->subject->add('foo', 'strtolower');

        DeepClosureComparator\DeepClosureAssert::assertEquals(
            $this->mapExpectedCallable('strtolower'),
            $this->subject->get('foo'),
        );
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
    public function getReturnsFunctionAcceptingNullableParameter(): void
    {
        $function = static fn(?Src\Renderer\RenderingContext $context) => $context?->getVariables()['baz'] ?? 'empty';
        $scope = [];
        $data = [];
        $options = new Handlebars\HelperOptions(
            $scope,
            $data,
            new Handlebars\RuntimeContext(),
            'foo',
            [
                'foo' => 'baz',
            ],
            0,
            static fn() => '',
            static fn() => '',
        );

        $this->subject->add('foo', $function);

        self::assertSame('empty', $this->subject->get('foo')('foo', $options));
    }

    #[Framework\Attributes\Test]
    public function getReturnsFunctionAcceptingMissingTypeForRuntimeArguments(): void
    {
        $function = static fn($foo) => $foo;
        $scope = [];
        $data = [];
        $options = new Handlebars\HelperOptions(
            $scope,
            $data,
            new Handlebars\RuntimeContext(),
            'foo',
            [
                'foo' => 'baz',
            ],
            0,
            static fn() => '',
            static fn() => '',
        );

        $this->subject->add('foo', $function);

        self::assertSame('foo', $this->subject->get('foo')('foo', $options));
    }

    #[Framework\Attributes\Test]
    public function getThrowsExceptionIfFunctionHasNonNullableParameterWithNullValue(): void
    {
        $function = static fn(Src\Renderer\RenderingContext $context) => $context->getVariables()['baz'];
        $scope = [];
        $data = [];
        $options = new Handlebars\HelperOptions(
            $scope,
            $data,
            new Handlebars\RuntimeContext(),
            'foo',
            [
                'foo' => 'baz',
            ],
            0,
            static fn() => '',
            static fn() => '',
        );

        $this->subject->add('foo', $function);

        $this->expectExceptionObject(
            Src\Exception\InvalidHelperException::forUnresolvableParameter($function, 'context'),
        );

        $this->subject->get('foo')('foo', $options);
    }

    #[Framework\Attributes\Test]
    public function getThrowsExceptionIfFunctionHasParameterWithUnsupportedType(): void
    {
        $function = static fn(Src\Renderer\RenderingContext $context) => $context->getVariables()['baz'];
        $scope = [];
        $data = [
            'renderingContext' => 'foo',
        ];
        $options = new Handlebars\HelperOptions(
            $scope,
            $data,
            new Handlebars\RuntimeContext(),
            'foo',
            [
                'foo' => 'baz',
            ],
            0,
            static fn() => '',
            static fn() => '',
        );

        $this->subject->add('foo', $function);

        $this->expectExceptionObject(
            Src\Exception\InvalidHelperException::forUnresolvableParameter($function, 'context'),
        );

        $this->subject->get('foo')('foo', $options);
    }

    #[Framework\Attributes\Test]
    public function getReturnsRegisteredHelper(): void
    {
        $this->subject->add('foo', 'trim');

        DeepClosureComparator\DeepClosureAssert::assertEquals(
            $this->mapExpectedCallable('trim'),
            $this->subject->get('foo'),
        );
    }

    #[Framework\Attributes\Test]
    public function getAllReturnsRegisteredHelpers(): void
    {
        self::assertSame([], $this->subject->getAll());

        $this->subject->add('foo', 'strtolower');

        DeepClosureComparator\DeepClosureAssert::assertEquals(
            ['foo' => $this->mapExpectedCallable('strtolower')],
            $this->subject->getAll(),
        );
    }

    #[Framework\Attributes\Test]
    public function hasReturnsTrueIfGivenHelperIsRegistered(): void
    {
        self::assertFalse($this->subject->has('foo'));

        $this->subject->add('foo', 'strtolower');

        self::assertTrue($this->subject->has('foo'));
    }

    /**
     * @return \Generator<string, array{array{object, string}|string}>
     */
    public static function addLogsCriticalErrorIfGivenHelperIsInvalidDataProvider(): \Generator
    {
        yield 'non-callable function as string' => ['foo_baz'];
        yield 'non-callable class method' => [Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper::class . '::foo'];
        yield 'non-callable class method in array syntax' => [[new Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper(), 'foo']];
        yield 'non-callable private class method' => [Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper::class . '::executeInternal'];
    }

    /**
     * @return \Generator<string, array{ValidHelperFunction, callable}>
     */
    public static function addRegistersHelperCorrectlyDataProvider(): \Generator
    {
        yield 'callable as string' => [
            'trim',
            'trim',
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
            (new Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper())->staticExecute(...),
        ];
        yield 'non-static class method as string' => [
            Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper::class . '::execute',
            (new Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper())->execute(...),
        ];
        yield 'static class method as array' => [
            [Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper::class, 'staticExecute'],
            (new Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper())->staticExecute(...),
        ];
        yield 'non-static class method as array' => [
            [Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper::class, 'execute'],
            (new Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper())->execute(...),
        ];
        yield 'class method as initialized array' => [
            [new Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper(), 'execute'],
            (new Tests\Unit\Fixtures\Classes\Renderer\Helper\DummyHelper())->execute(...),
        ];
    }

    /**
     * @return \Generator<string, array{callable, string}>
     */
    public static function addDecoratesHelperFunctionDataProvider(): \Generator
    {
        yield 'no parameters' => [
            static fn() => 'foo',
            'foo',
        ];
        yield 'helper options only' => [
            static fn(Handlebars\HelperOptions $options) => $options->hash['foo'],
            'baz',
        ];
        yield 'helper options and arguments' => [
            static function (Handlebars\HelperOptions $options, string $foo) {
                self::assertIsString($options->hash['foo']);

                return $foo . $options->hash['foo'];
            },
            'foobaz',
        ];
        yield 'rendering context only' => [
            static fn(Src\Renderer\RenderingContext $context) => $context->getVariables()['baz'],
            'foo',
        ];
        yield 'rendering context and arguments' => [
            static function (Src\Renderer\RenderingContext $context, string $foo) {
                self::assertIsString($context->getVariables()['baz']);

                return $foo . $context->getVariables()['baz'];
            },
            'foofoo',
        ];
        yield 'rendering context, helper options and arguments' => [
            static function (Src\Renderer\RenderingContext $context, Handlebars\HelperOptions $options, string $foo) {
                self::assertIsString($options->hash['foo']);
                self::assertIsString($context->getVariables()['baz']);

                return $foo . $options->hash['foo'] . $context->getVariables()['baz'];
            },
            'foobazfoo',
        ];
        yield 'helper options, rendering context and arguments' => [
            static function (Handlebars\HelperOptions $options, Src\Renderer\RenderingContext $context, string $foo) {
                self::assertIsString($options->hash['foo']);
                self::assertIsString($context->getVariables()['baz']);

                return $foo . $options->hash['foo'] . $context->getVariables()['baz'];
            },
            'foobazfoo',
        ];
    }

    private function mapExpectedCallable(callable $expectedCallable): \Closure
    {
        $reflectionObject = new \ReflectionObject($this->subject);
        $reflectionMethod = $reflectionObject->getMethod('decorateHelperFunction');
        $invocationResult = $reflectionMethod->invoke($this->subject, $expectedCallable);

        self::assertInstanceOf(\Closure::class, $invocationResult);

        return $invocationResult;
    }
}
