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

namespace CPSIT\Typo3Handlebars\Tests\Functional\Renderer\Variables;

use CPSIT\Typo3Handlebars as Src;
use CPSIT\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\CMS\Frontend;
use TYPO3\TestingFramework;

/**
 * TypoScriptVariableProviderTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Variables\TypoScriptVariableProvider::class)]
final class TypoScriptVariableProviderTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\FrontendRequestTrait;

    private Tests\Unit\Fixtures\Classes\DummyConfigurationManager $configurationManager;
    private Src\Renderer\Variables\TypoScriptVariableProvider $subject;
    private \Psr\Http\Message\ServerRequestInterface $request;

    public function setUp(): void
    {
        parent::setUp();

        $this->configurationManager = new Tests\Unit\Fixtures\Classes\DummyConfigurationManager();
        $this->configurationManager->configuration = [
            'variables' => [
                'foo' => [
                    '_typoScriptNodeValue' => 'TEXT',
                    'value' => 'baz',
                ],
            ],
            'dataProcessing' => [
                '10' => [
                    '_typoScriptNodeValue' => 'split',
                    'fieldName' => 'foo',
                    'delimiter' => ',',
                    'as' => 'baz',
                ],
            ],
        ];

        $this->subject = new Src\Renderer\Variables\TypoScriptVariableProvider(
            $this->configurationManager,
            $this->get(Frontend\ContentObject\ContentDataProcessor::class),
            $this->get(Core\TypoScript\TypoScriptService::class),
        );

        $cObj = $this->get(Frontend\ContentObject\ContentObjectRenderer::class);
        $cObj->data = [
            'foo' => '1,2,3',
        ];

        $this->request = $this->buildServerRequest();
        $this->request = $this->request->withAttribute('currentContentObject', $cObj);
    }

    #[Framework\Attributes\Test]
    public function getReturnsEmptyArrayIfContentObjectRendererCannotBeResolved(): void
    {
        self::assertSame([], $this->subject->get());
    }

    #[Framework\Attributes\Test]
    public function getReturnsEmptyArrayIfResolvedVariablesAreInvalid(): void
    {
        $this->configurationManager->configuration = [
            'variables' => 'foo',
        ];

        $this->subject->setRequest($this->request);

        self::assertSame([], $this->subject->get());
    }

    #[Framework\Attributes\Test]
    public function getReturnsVariablesFetchedViaConfigurationManager(): void
    {
        $this->subject->setRequest($this->request);

        $expected = [
            'foo' => 'baz',
            'baz' => ['1', '2', '3'],
        ];

        self::assertSame($expected, $this->subject->get());
    }

    #[Framework\Attributes\Test]
    public function getCachesFetchedVariables(): void
    {
        $this->subject->setRequest($this->request);

        $expected = [
            'foo' => 'baz',
            'baz' => ['1', '2', '3'],
        ];

        self::assertSame($expected, $this->subject->get());

        $this->configurationManager->configuration = [];

        self::assertSame($expected, $this->subject->get());
    }

    #[Framework\Attributes\Test]
    public function isCacheableReturnsFalseIfRequestIsNotAttached(): void
    {
        self::assertFalse($this->subject->isCacheable());

        $this->subject->setRequest($this->request);

        self::assertTrue($this->subject->isCacheable());
    }

    #[Framework\Attributes\Test]
    public function objectCanBeAccessedAsReadOnlyArray(): void
    {
        $this->subject->setRequest($this->request);

        // offsetExists
        self::assertTrue(isset($this->subject['foo']));
        self::assertTrue(isset($this->subject['baz']));
        self::assertFalse(isset($this->subject['boo']));

        // offsetGet
        self::assertSame('baz', $this->subject['foo']);
        self::assertSame(['1', '2', '3'], $this->subject['baz']);
        self::assertNull($this->subject['boo']);
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
