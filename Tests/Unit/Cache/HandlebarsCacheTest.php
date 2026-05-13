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

namespace CPSIT\Typo3Handlebars\Tests\Unit\Cache;

use CPSIT\Typo3Handlebars as Src;
use CPSIT\Typo3Handlebars\Tests;
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
    use Tests\HandlebarsCacheTrait;

    private Src\Cache\HandlebarsCache $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $cache = $this->getCache();
        $cacheStub = self::createStub(Core\Cache\Frontend\FrontendInterface::class);
        $cacheStub->method('get')->willReturnCallback(
            static fn(string $entryIdentifier) => $cache->get(new Src\Cache\CacheContext($entryIdentifier)),
        );
        $cacheStub->method('set')->willReturnCallback(
            static function (string $entryIdentifier, mixed $data) use ($cache) {
                self::assertIsString($data);

                $cache->set(new Src\Cache\CacheContext($entryIdentifier), $data);
            },
        );

        $this->subject = new Src\Cache\HandlebarsCache($cacheStub);
    }

    #[Framework\Attributes\Test]
    public function getReturnsNullIfTemplateIsNotCached(): void
    {
        $this->clearCache();

        $context = new Src\Cache\CacheContext('foo');

        self::assertNull($this->subject->get($context));
    }

    #[Framework\Attributes\Test]
    public function getReturnsCachedTemplate(): void
    {
        $context = new Src\Cache\CacheContext('foo');

        $this->subject->set($context, 'hello world');

        self::assertSame('hello world', $this->subject->get($context));
    }

    protected function tearDown(): void
    {
        self::assertTrue($this->clearCache(), 'Unable to clear Handlebars cache.');
        parent::tearDown();
    }
}
