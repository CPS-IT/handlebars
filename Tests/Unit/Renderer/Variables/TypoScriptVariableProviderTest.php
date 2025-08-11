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

namespace CPSIT\Typo3Handlebars\Tests\Unit\Renderer\Variables;

use CPSIT\Typo3Handlebars as Src;
use CPSIT\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * TypoScriptVariableProviderTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Variables\TypoScriptVariableProvider::class)]
final class TypoScriptVariableProviderTest extends TestingFramework\Core\Unit\UnitTestCase
{
    private Tests\Unit\Fixtures\Classes\DummyConfigurationManager $configurationManager;
    private Src\Renderer\Variables\TypoScriptVariableProvider $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->configurationManager = new Tests\Unit\Fixtures\Classes\DummyConfigurationManager();
        $this->configurationManager->configuration = [
            'variables' => [
                'foo' => 'baz',
            ],
        ];

        $this->subject = new Src\Renderer\Variables\TypoScriptVariableProvider($this->configurationManager);
    }

    #[Framework\Attributes\Test]
    public function getReturnsVariablesFetchedViaConfigurationManager(): void
    {
        self::assertSame(['foo' => 'baz'], $this->subject->get());
    }

    #[Framework\Attributes\Test]
    public function getCachesFetchedVariables(): void
    {
        self::assertSame(['foo' => 'baz'], $this->subject->get());

        $this->configurationManager->configuration = [];

        self::assertSame(['foo' => 'baz'], $this->subject->get());
    }

    #[Framework\Attributes\Test]
    public function objectCanBeAccessedAsReadOnlyArray(): void
    {
        // offsetExists
        self::assertTrue(isset($this->subject['foo']));
        self::assertFalse(isset($this->subject['baz']));

        // offsetGet
        self::assertSame('baz', $this->subject['foo']);
        self::assertNull($this->subject['baz']);
    }

    #[Framework\Attributes\Test]
    public function offsetSetThrowsLogicException(): void
    {
        $this->expectExceptionObject(
            new \LogicException('Variables cannot be modified.', 1736274326),
        );

        $this->subject['baz'] = 'foo';
    }

    #[Framework\Attributes\Test]
    public function offsetUnsetThrowsLogicException(): void
    {
        $this->expectExceptionObject(
            new \LogicException('Variables cannot be modified.', 1736274336),
        );

        unset($this->subject['foo']);
    }
}
