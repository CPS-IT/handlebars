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

use Fr\Typo3Handlebars\Tests\Unit\Fixtures\Classes\Traits\DummyHandlebarsHelperTraitClass;
use Fr\Typo3Handlebars\Traits\HandlebarsHelperTrait;
use Psr\Log\Test\TestLogger;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * HandlebarsHelperTraitTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class HandlebarsHelperTraitTest extends UnitTestCase
{
    /**
     * @var TestLogger
     */
    protected $logger;

    /**
     * @var DummyHandlebarsHelperTraitClass
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = new TestLogger();
        $this->subject = new DummyHandlebarsHelperTraitClass();
        $this->subject->setLogger($this->logger);
    }

    /**
     * @test
     * @dataProvider registerHelperLogsCriticalErrorIfGivenHelperIsInvalidDataProvider
     * @param mixed $function
     */
    public function registerHelperLogsCriticalErrorIfGivenHelperIsInvalid($function): void
    {
        $this->subject->registerHelper('foo', $function);
        self::assertTrue($this->logger->hasCriticalThatPasses(function ($logRecord) use ($function) {
            static::assertSame('Error while registering Handlebars helper "foo".', $logRecord['message']);
            static::assertSame('foo', $logRecord['context']['name']);
            static::assertSame($function, $logRecord['context']['function']);
            return true;
        }));
        self::assertSame([], $this->subject->getHelpers());
    }

    /**
     * @test
     * @dataProvider registerHelperRegistersHelperCorrectlyDataProvider
     * @param mixed $function
     * @param string|callable $expectedCallable
     */
    public function registerHelperRegistersHelperCorrectly($function, $expectedCallable): void
    {
        $this->subject->registerHelper('foo', $function);
        self::assertEquals(['foo' => $expectedCallable], $this->subject->getHelpers());
    }

    /**
     * @test
     */
    public function registerHelperOverridesAvailableHelper(): void
    {
        $this->subject->registerHelper('foo', 'trim');
        self::assertSame(['foo' => 'trim'], $this->subject->getHelpers());

        $this->subject->registerHelper('foo', 'strtolower');
        self::assertSame(['foo' => 'strtolower'], $this->subject->getHelpers());
    }

    /**
     * @test
     */
    public function getHelpersReturnsRegisteredHelpers(): void
    {
        self::assertSame([], $this->subject->getHelpers());

        $this->subject->registerHelper('foo', 'strtolower');
        self::assertSame(['foo' => 'strtolower'], $this->subject->getHelpers());
    }

    /**
     * @return \Generator<string, array<mixed>>
     */
    public function registerHelperLogsCriticalErrorIfGivenHelperIsInvalidDataProvider(): \Generator
    {
        yield 'null value' => [null];
        yield 'non-callable function as string' => ['foo_baz'];
        yield 'non-callable class method' => [HandlebarsHelperTrait::class . '::isValidHelper'];
        yield 'non-callable class method in array syntax' => [[$this->subject, 'isValidHelper']];
    }

    /**
     * @return \Generator<string, array<mixed>>
     */
    public function registerHelperRegistersHelperCorrectlyDataProvider(): \Generator
    {
        yield 'callable function as string' => [
            'trim',
            'trim',
        ];
        yield 'callable class method' => [
            self::class . '::assertSame',
            [new self(), 'assertSame'],
        ];
        yield 'callable class method in array syntax' => [
            [$this, 'assertSame'],
            [$this, 'assertSame'],
        ];
    }
}
