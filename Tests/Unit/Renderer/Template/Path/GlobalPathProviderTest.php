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
 * GlobalPathProviderTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Template\Path\GlobalPathProvider::class)]
final class GlobalPathProviderTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Src\Renderer\Template\Path\GlobalPathProvider $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\Renderer\Template\Path\GlobalPathProvider([
            Src\Renderer\Template\Path\PathProvider::PARTIALS => [
                0 => 'partialFoo',
                10 => 'partialBaz',
            ],
            Src\Renderer\Template\Path\PathProvider::TEMPLATES => [
                0 => 'templateFoo',
                10 => 'templateBaz',
            ],
        ]);
    }

    #[Framework\Attributes\Test]
    public function getPartialRootPathsReturnsPartialRootPaths(): void
    {
        $expected = [
            0 => 'partialFoo',
            10 => 'partialBaz',
        ];

        self::assertSame($expected, $this->subject->getPartialRootPaths());
    }

    #[Framework\Attributes\Test]
    public function getTemplateRootPathsReturnsTemplateRootPaths(): void
    {
        $expected = [
            0 => 'templateFoo',
            10 => 'templateBaz',
        ];

        self::assertSame($expected, $this->subject->getTemplateRootPaths());
    }
}
