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

use Composer\InstalledVersions;
use CPSIT\Typo3Handlebars as Src;
use DevTheorem\Handlebars;
use PHPUnit\Framework;
use Symfony\Component\Filesystem;
use TYPO3\TestingFramework;

/**
 * CacheContextTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Cache\CacheContext::class)]
final class CacheContextTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Filesystem\Filesystem $filesystem;
    private Src\Cache\CacheContext $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new Filesystem\Filesystem();
        $this->subject = new Src\Cache\CacheContext(
            'foo',
            new Handlebars\Options(compat: true),
        );
    }

    #[Framework\Attributes\Test]
    public function calculateCacheIdentifierTakesPackageVersionFromInstalledPhpIntoAccount(): void
    {
        $this->filesystem->copy(
            \dirname(__DIR__) . '/Fixtures/Files/installed.php',
            \dirname(__DIR__, 3) . '/Resources/Private/Libs/vendor/composer/installed.php',
        );

        $expected = \sha1(
            \serialize([
                'foo',
                $this->subject->options,
                '1.0.0.0',
            ]),
        );

        self::assertSame($expected, $this->subject->calculateCacheIdentifier());
    }

    #[Framework\Attributes\Test]
    public function calculateCacheIdentifierTakesPackageVersionFromInstalledVersionsClassIntoAccount(): void
    {
        $this->filesystem->remove(
            \dirname(__DIR__, 3) . '/Resources/Private/Libs/vendor/composer/installed.php',
        );

        $expected = \sha1(
            \serialize([
                'foo',
                $this->subject->options,
                InstalledVersions::getVersion('devtheorem/php-handlebars'),
            ]),
        );

        self::assertSame($expected, $this->subject->calculateCacheIdentifier());
    }

    #[Framework\Attributes\Test]
    public function cloneResetPreviouslyCalculatedCacheIdentifier(): void
    {
        // Make sure installed.php does not exist
        $this->filesystem->remove(
            \dirname(__DIR__, 3) . '/Resources/Private/Libs/vendor/composer/installed.php',
        );

        $cacheIdentifier = $this->subject->calculateCacheIdentifier();

        // Trigger package version change
        $this->filesystem->copy(
            \dirname(__DIR__) . '/Fixtures/Files/installed.php',
            \dirname(__DIR__, 3) . '/Resources/Private/Libs/vendor/composer/installed.php',
        );

        $clone = clone $this->subject;

        self::assertNotSame($cacheIdentifier, $clone->calculateCacheIdentifier());
    }
}
