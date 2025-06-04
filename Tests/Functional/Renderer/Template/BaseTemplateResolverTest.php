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

namespace Fr\Typo3Handlebars\Tests\Functional\Renderer\Template;

use Fr\Typo3Handlebars as Src;
use Fr\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * BaseTemplateResolverTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Template\BaseTemplateResolver::class)]
final class BaseTemplateResolverTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'handlebars',
        'test_extension',
    ];

    protected array $configurationToUseInTestInstance = [
        'BE' => [
            'lockRootPath' => [
                '/foo',
            ],
        ],
    ];

    protected bool $initializeDatabase = false;

    private Tests\Unit\Fixtures\Classes\Renderer\Template\DummyTemplateResolver $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = new Tests\Unit\Fixtures\Classes\Renderer\Template\DummyTemplateResolver();
    }

    #[Framework\Attributes\Test]
    public function supportsIndicatesSupportOfGivenFileExtension(): void
    {
        self::assertFalse($this->subject->supports('jpeg'));
        self::assertTrue($this->subject->supports('hbs'));
        // File extensions are case-sensitive
        self::assertFalse($this->subject->supports('HBS'));
    }

    #[Framework\Attributes\Test]
    public function resolveFilenameCanResolveExtensionReferences(): void
    {
        $expected = $this->instancePath . '/typo3conf/ext/test_extension/Resources/Templates/foo.hbs';

        self::assertSame(
            $expected,
            $this->subject->resolveFilename('EXT:test_extension/Resources/Templates/foo.hbs'),
        );
    }

    #[Framework\Attributes\Test]
    public function resolveFilenameCanResolveExtensionReferencesWithGivenRootPath(): void
    {
        $expected = $this->instancePath . '/typo3conf/ext/test_extension/Resources/Templates/foo.hbs';

        self::assertSame(
            $expected,
            $this->subject->resolveFilename('foo.hbs', 'EXT:test_extension/Resources/Templates'),
        );
    }

    #[Framework\Attributes\Test]
    public function resolveFilenameReturnsCanResolveSupportedAbsolutePaths(): void
    {
        self::assertSame('/foo/baz.hbs', $this->subject->resolveFilename('/foo/baz.hbs'));
    }

    #[Framework\Attributes\Test]
    public function resolveFilenameAppendsGivenExtensionOnUnsupportedExtension(): void
    {
        $expected = $this->instancePath . '/typo3conf/ext/test_extension/Resources/Templates/foo.baz.hbs';

        self::assertSame(
            $expected,
            $this->subject->resolveFilename('foo.baz', 'EXT:test_extension/Resources/Templates', 'hbs'),
        );
    }

    #[Framework\Attributes\Test]
    public function resolveFilenameReturnsEmptyStringOnUnsupportedAbsolutePath(): void
    {
        self::assertSame('', $this->subject->resolveFilename('/baz/foo.hbs'));
    }

    #[Framework\Attributes\Test]
    public function resolveTemplatePathsThrowsExceptionIfRootPathIsMalicious(): void
    {
        $templatePaths = new Src\Renderer\Template\TemplatePaths([
            /* @phpstan-ignore argument.type */
            new Tests\Unit\Fixtures\Classes\Renderer\Template\Path\DummyPathProvider([null]),
        ]);

        $this->expectExceptionObject(
            new Src\Exception\RootPathIsMalicious(null),
        );

        $this->subject->resolveTemplatePaths($templatePaths);
    }

    #[Framework\Attributes\Test]
    public function resolveTemplatePathsThrowsExceptionIfAbsoluteRootPathIsNotAllowed(): void
    {
        $templatePaths = new Src\Renderer\Template\TemplatePaths([
            new Tests\Unit\Fixtures\Classes\Renderer\Template\Path\DummyPathProvider(['/baz']),
        ]);

        $this->expectExceptionObject(
            new Src\Exception\RootPathIsNotResolvable('/baz'),
        );

        $this->subject->resolveTemplatePaths($templatePaths);
    }

    #[Framework\Attributes\Test]
    public function resolveTemplatePathsReturnsNormalizedTemplateRootPathsAndPartialRootPaths(): void
    {
        $templatePaths = new Src\Renderer\Template\TemplatePaths([
            new Tests\Unit\Fixtures\Classes\Renderer\Template\Path\DummyPathProvider(
                [
                    10 => 'EXT:test_extension/Resources/Private/Templates',
                    5 => '/foo/templates',
                ],
                [
                    2 => 'EXT:test_extension/Resources/Private/Partials',
                    7 => '/foo/partials',
                ],
            ),
        ]);

        $expected = [
            [
                '/foo/templates',
                $this->instancePath . '/typo3conf/ext/test_extension/Resources/Private/Templates',
            ],
            [
                $this->instancePath . '/typo3conf/ext/test_extension/Resources/Private/Partials',
                '/foo/partials',
            ],
        ];

        self::assertSame($expected, $this->subject->resolveTemplatePaths($templatePaths));
    }

    #[Framework\Attributes\Test]
    public function resolveSupportedFileExtensionsThrowsExceptionOnMaliciousFileExtension(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\FileExtensionIsMalicious(null),
        );

        /* @phpstan-ignore argument.type */
        $this->subject->resolveSupportedFileExtensions([null]);
    }

    #[Framework\Attributes\Test]
    public function resolveSupportedFileExtensionsThrowsExceptionOnInvalidFileExtension(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\FileExtensionIsInvalid('f?o!'),
        );

        $this->subject->resolveSupportedFileExtensions(['f?o!']);
    }

    #[Framework\Attributes\Test]
    public function resolveSupportedFileExtensionsUsesDefaultFileExtensionsIfGivenFileExtensionsAreEmpty(): void
    {
        self::assertSame(['hbs', 'handlebars', 'html'], $this->subject->resolveSupportedFileExtensions([]));
    }

    #[Framework\Attributes\Test]
    public function resolveSupportedFileExtensionsStripsLeadingDot(): void
    {
        self::assertSame(['hbs', 'handlebars'], $this->subject->resolveSupportedFileExtensions(['.hbs', 'handlebars']));
    }

    #[Framework\Attributes\Test]
    public function resolveSupportedFileExtensionsReturnsUniqueList(): void
    {
        self::assertSame(['hbs'], $this->subject->resolveSupportedFileExtensions(['hbs', '.hbs', 'hbs']));
    }
}
