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
use Fr\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * TypoScriptPathProviderTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Template\Path\TypoScriptPathProvider::class)]
final class TypoScriptPathProviderTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Tests\Unit\Fixtures\Classes\DummyConfigurationManager $configurationManager;
    private Src\Renderer\Template\Path\TypoScriptPathProvider $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->configurationManager = new Tests\Unit\Fixtures\Classes\DummyConfigurationManager();
        $this->subject = new Src\Renderer\Template\Path\TypoScriptPathProvider($this->configurationManager);
    }

    #[Framework\Attributes\Test]
    public function getPartialRootPathsReturnsPartialRootPaths(): void
    {
        $this->configurationManager->configuration = [
            'view' => [
                'partialRootPaths' => [
                    0 => 'partialFoo',
                    10 => 'partialBaz',
                ],
            ],
        ];

        $expected = [
            0 => 'partialFoo',
            10 => 'partialBaz',
        ];

        self::assertSame($expected, $this->subject->getPartialRootPaths());
    }

    #[Framework\Attributes\Test]
    public function getTemplateRootPathsReturnsTemplateRootPaths(): void
    {
        $this->configurationManager->configuration = [
            'view' => [
                'templateRootPaths' => [
                    0 => 'templateFoo',
                    10 => 'templateBaz',
                ],
            ],
        ];

        $expected = [
            0 => 'templateFoo',
            10 => 'templateBaz',
        ];

        self::assertSame($expected, $this->subject->getTemplateRootPaths());
    }
}
