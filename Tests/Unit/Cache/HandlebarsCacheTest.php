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

use Fr\Typo3Handlebars as Src;
use Fr\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\TestingFramework;

/**
 * HandlebarsCacheTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Cache\HandlebarsCache::class)]
final class HandlebarsCacheTest extends TestingFramework\Core\Unit\UnitTestCase
{
    use Tests\Unit\HandlebarsCacheTrait;

    private Src\Cache\HandlebarsCache $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $cache = $this->getCache();
        $cacheMock = $this->createMock(Core\Cache\Frontend\FrontendInterface::class);
        $cacheMock->method('get')->willReturnCallback(
            static fn(string $entryIdentifier) => $cache->get($entryIdentifier),
        );
        $cacheMock->method('set')->willReturnCallback(
            static function (string $entryIdentifier, mixed $data) use ($cache) {
                $cache->set($entryIdentifier, $data);
            },
        );

        $this->subject = new Src\Cache\HandlebarsCache($cacheMock);
    }

    #[Framework\Attributes\Test]
    public function getReturnsNullIfTemplateIsNotCached(): void
    {
        $this->clearCache();
        self::assertNull($this->subject->get('foo'));
    }

    #[Framework\Attributes\Test]
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
