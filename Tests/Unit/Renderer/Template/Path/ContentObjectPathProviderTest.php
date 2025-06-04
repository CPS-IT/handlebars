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

namespace Fr\Typo3Handlebars\Tests\Unit\Renderer\Template\Path;

use Fr\Typo3Handlebars as Src;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * ContentObjectPathProviderTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Template\Path\ContentObjectPathProvider::class)]
final class ContentObjectPathProviderTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private const CONFIGURATION_1 = [
        'templateRootPaths' => [
            10 => 'foo',
        ],
        'partialRootPaths' => [
            10 => 'baz',
        ],
        'templateRootPath' => 'foo with higher priority',
        'partialRootPath' => 'baz with higher priority',
    ];
    private const CONFIGURATION_2 = [
        'templateRootPaths' => [
            20 => 'another foo',
        ],
        'partialRootPaths' => [
            20 => 'another baz',
        ],
        'templateRootPath' => 'another foo with higher priority',
        'partialRootPath' => 'another baz with higher priority',
    ];

    private Src\Renderer\Template\Path\ContentObjectPathProvider $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\Renderer\Template\Path\ContentObjectPathProvider();
    }

    #[Framework\Attributes\Test]
    public function pushAddsConfigurationToStack(): void
    {
        $this->subject->push(self::CONFIGURATION_1);

        self::assertFalse($this->subject->isEmpty());
        self::assertSame(
            [
                10 => 'foo',
                PHP_INT_MAX => 'foo with higher priority',
            ],
            $this->subject->getTemplateRootPaths(),
        );
        self::assertSame(
            [
                10 => 'baz',
                PHP_INT_MAX => 'baz with higher priority',
            ],
            $this->subject->getPartialRootPaths(),
        );
    }

    #[Framework\Attributes\Test]
    public function popRemovesCurrentConfigurationFromStack(): void
    {
        $this->subject->push(self::CONFIGURATION_1);
        $this->subject->push(self::CONFIGURATION_2);
        $this->subject->pop();

        self::assertFalse($this->subject->isEmpty());

        $this->subject->pop();

        self::assertTrue($this->subject->isEmpty());
    }

    #[Framework\Attributes\Test]
    public function popDoesNothingIfStackIsAlreadyEmpty(): void
    {
        $this->subject->pop();

        self::assertTrue($this->subject->isEmpty());
    }

    #[Framework\Attributes\Test]
    public function getPartialRootPathsReturnsEmptyArrayIfStackIsEmpty(): void
    {
        self::assertSame([], $this->subject->getPartialRootPaths());
    }

    #[Framework\Attributes\Test]
    public function getPartialRootPathsReturnsMergedRootPathsFromStack(): void
    {
        $this->subject->push(self::CONFIGURATION_1);
        $this->subject->push(self::CONFIGURATION_2);

        self::assertSame(
            [
                10 => 'baz',
                20 => 'another baz',
                PHP_INT_MAX => 'another baz with higher priority',
            ],
            $this->subject->getPartialRootPaths(),
        );
    }

    #[Framework\Attributes\Test]
    public function getTemplateRootPathsReturnsEmptyArrayIfStackIsEmpty(): void
    {
        self::assertSame([], $this->subject->getTemplateRootPaths());
    }

    #[Framework\Attributes\Test]
    public function getTemplateRootPathsReturnsMergedRootPathsFromStack(): void
    {
        $this->subject->push(self::CONFIGURATION_1);
        $this->subject->push(self::CONFIGURATION_2);

        self::assertSame(
            [
                10 => 'foo',
                20 => 'another foo',
                PHP_INT_MAX => 'another foo with higher priority',
            ],
            $this->subject->getTemplateRootPaths(),
        );
    }
}
