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
use PHPUnit\Framework\MockObject\MockObject;
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

    /**
     * @var FrontendInterface&MockObject
     */
    protected $cacheMock;

    /**
     * @var HandlebarsCache
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $cache = $this->getCache();

        $this->cacheMock = $this->createMock(FrontendInterface::class);
        $this->cacheMock->method('get')->willReturnCallback(function (string $entryIdentifier) use ($cache) {
            return $cache->get($entryIdentifier);
        });
        $this->cacheMock->method('set')->willReturnCallback(function (string $entryIdentifier, $data) use ($cache) {
            $cache->set($entryIdentifier, $data);
        });
        $this->subject = new HandlebarsCache($this->cacheMock);
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
