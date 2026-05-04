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
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Frontend;
use TYPO3\TestingFramework;

/**
 * VariablesProcessorTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Variables\VariablesProcessor::class)]
final class VariablesProcessorTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\FrontendRequestTrait;

    protected array $testExtensionsToLoad = [
        'handlebars',
    ];

    private Frontend\ContentObject\ContentObjectRenderer $contentObjectRenderer;
    private Src\Renderer\Variables\VariablesProcessor $subject;

    public function setUp(): void
    {
        parent::setUp();

        $request = $this->buildServerRequest();

        $this->contentObjectRenderer = $this->get(Frontend\ContentObject\ContentObjectRenderer::class);
        $this->contentObjectRenderer->setRequest($request);
        $this->get(Extbase\Configuration\ConfigurationManagerInterface::class)->setRequest($request);
        $this->subject = Src\Renderer\Variables\VariablesProcessor::for($this->contentObjectRenderer);
    }

    #[Framework\Attributes\Test]
    public function processResolvesAndAppliesContentObjectVariablesFromConfig(): void
    {
        $expected = [
            'foo' => 'baz',
        ];

        $actual = $this->subject->process([
            'foo' => 'TEXT',
            'foo.' => [
                'value' => 'baz',
            ],
        ]);

        self::assertEquals($expected, $actual);
    }

    #[Framework\Attributes\Test]
    public function processThrowsExceptionWhenReservedVariablesAreUsed(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\ReservedVariableCannotBeUsed('data'),
        );

        $this->subject->process([
            'data' => 'TEXT',
            'data.' => [
                'value' => 'baz',
            ],
        ]);
    }

    #[Framework\Attributes\Test]
    public function processResolvesAndAppliesSimpleVariablesFromConfig(): void
    {
        $expected = [
            'foo' => [
                'baz' => 'boo',
            ],
            'baz' => new \stdClass(),
        ];

        $actual = $this->subject->process([
            'foo.' => [
                'baz' => 'boo',
            ],
            'baz' => new \stdClass(),
        ]);

        self::assertEquals($expected, $actual);
    }

    #[Framework\Attributes\Test]
    public function processResolvesAndAppliesMixedVariablesFromConfig(): void
    {
        $expected = [
            'foo' => [
                'baz' => 'boo',
            ],
        ];

        $actual = $this->subject->process([
            'foo.' => [
                'baz' => 'TEXT',
                'baz.' => [
                    'value' => 'boo',
                ],
            ],
        ]);

        self::assertEquals($expected, $actual);
    }

    #[Framework\Attributes\Test]
    public function processResolvesReferencedVariablesFromConfig(): void
    {
        $expected = [
            'foo' => 'boo',
        ];

        $astBuilder = new Core\TypoScript\AST\AstBuilder(new Core\EventDispatcher\NoopEventDispatcher());
        $factory = $this->get(Core\TypoScript\TypoScriptStringFactory::class);
        $rootNode = $factory->parseFromString('', $astBuilder);

        $frontendTypoScript = new Core\TypoScript\FrontendTypoScript($rootNode, [], [], []);
        $frontendTypoScript->setSetupTree($rootNode);
        $frontendTypoScript->setSetupArray([
            'fooContext' => 'TEXT',
            'fooContext.' => [
                'value' => 'boo',
            ],
        ]);

        $this->contentObjectRenderer->setRequest(
            $this->contentObjectRenderer->getRequest()->withAttribute('frontend.typoscript', $frontendTypoScript),
        );

        $actual = $this->subject->process([
            'foo' => '< fooContext',
        ]);

        self::assertEquals($expected, $actual);
    }

    #[Framework\Attributes\Test]
    public function processTreatsStringsWithLeftAngleBracketAndNonFollowingWhitespaceAsStaticText(): void
    {
        $expected = [
            'foo' => '<foo>',
        ];

        $actual = $this->subject->process([
            'foo' => '<foo>',
        ]);

        self::assertEquals($expected, $actual);
    }

    #[Framework\Attributes\Test]
    public function processDoesNotApplyContentObjectVariablesOnMatchingRemoveIfConfig(): void
    {
        $expected = [
            'foo' => [],
        ];

        $actual = $this->subject->process([
            'foo.' => [
                'baz' => 'TEXT',
                'baz.' => [
                    'value' => '',
                    'removeIf.' => [
                        'isFalse.' => [
                            'current' => '1',
                        ],
                    ],
                ],
            ],
        ]);

        self::assertEquals($expected, $actual);
    }

    #[Framework\Attributes\Test]
    public function processDoesNotApplySimpleVariablesOnMatchingRemoveIfConfig(): void
    {
        $this->contentObjectRenderer->data = [
            'foo' => '',
        ];

        $expected = [];

        $actual = $this->subject->process([
            'foo.' => [
                'baz.' => [
                    'foo' => 'TEXT',
                    'foo.' => [
                        'field' => 'foo',
                    ],
                ],
                'removeIf.' => [
                    'isFalse.' => [
                        'field' => 'foo',
                    ],
                ],
            ],
        ]);

        self::assertEquals($expected, $actual);
    }

    #[Framework\Attributes\Test]
    public function processRemovesRemoveIfConfigurationOnNonMatchingRemoveIfConfig(): void
    {
        $this->contentObjectRenderer->data = [
            'foo' => 'foo',
        ];

        $expected = [
            'foo' => [
                'baz' => [
                    'foo' => 'foo',
                ],
            ],
        ];

        $actual = $this->subject->process([
            'foo.' => [
                'baz.' => [
                    'foo' => 'TEXT',
                    'foo.' => [
                        'field' => 'foo',
                    ],
                ],
                'removeIf.' => [
                    'isFalse.' => [
                        'field' => 'foo',
                    ],
                ],
            ],
        ]);

        self::assertEquals($expected, $actual);
    }
}
