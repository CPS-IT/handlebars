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

namespace Fr\Typo3Handlebars\Tests\Unit\Cache;

use Fr\Typo3Handlebars\Cache\HandlebarsCache;
use Fr\Typo3Handlebars\Tests\Unit\HandlebarsCacheTrait;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * HandlebarsCacheTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class HandlebarsCacheTest extends UnitTestCase
{
    use HandlebarsCacheTrait;
    use ProphecyTrait;

    /**
     * @var ObjectProphecy|FrontendInterface
     */
    protected $cacheProphecy;

    /**
     * @var HandlebarsCache
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $cache = $this->getCache();
        $this->cacheProphecy = $this->prophesize(FrontendInterface::class);
        $this->cacheProphecy->get(Argument::type('string'))->will(function (array $parameters) use ($cache) {
            $cachedTemplate = $cache->get($parameters[0]);
            return $cachedTemplate;
        });
        $this->cacheProphecy->set(Argument::type('string'), Argument::type('string'))->will(function (array $parameters) use ($cache) {
            $cache->set($parameters[0], $parameters[1]);
        });
        $this->subject = new HandlebarsCache($this->cacheProphecy->reveal());
    }

    /**
     * @test
     */
    public function getReturnsNullIfTemplateIsNotCached(): void
    {
        $this->clearCache();
        self::assertNull($this->subject->get('foo'));
    }

    /**
     * @test
     */
    public function getReturnsCachedTemplate(): void
    {
        $this->subject->set('foo', 'hello world');
        self::assertSame('hello world', $this->subject->get('foo'));
    }

    protected function tearDown(): void
    {
        self::assertTrue($this->clearCache(), 'Unable to clear Handlebars cache.');
        parent::tearDown();
    }
}
