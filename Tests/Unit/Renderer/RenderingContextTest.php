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

namespace CPSIT\Typo3Handlebars\Tests\Unit\Renderer;

use CPSIT\Typo3Handlebars as Src;
use CPSIT\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * RenderingContextTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\RenderingContext::class)]
final class RenderingContextTest extends TestingFramework\Core\Unit\UnitTestCase
{
    use Tests\HandlebarsTemplateResolverTrait;

    private Src\Renderer\RenderingContext $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\Renderer\RenderingContext(
            'DummyTemplate',
            [
                'foo' => 'baz',
            ],
        );
    }

    #[Framework\Attributes\Test]
    public function getTemplateThrowsExceptionIfNeitherTemplatePathNorTemplateSourceAreDefined(): void
    {
        $subject = new Src\Renderer\RenderingContext();

        $this->expectExceptionObject(
            new Src\Exception\ViewIsNotProperlyInitialized(),
        );

        $subject->getTemplate();
    }

    #[Framework\Attributes\Test]
    public function getTemplateReturnsTemplateSourceIfDefined(): void
    {
        $this->subject->setTemplateSource('baz');

        self::assertSame('baz', $this->subject->getTemplate());
    }

    #[Framework\Attributes\Test]
    public function getTemplateThrowsExceptionIfTemplatePathIsInvalid(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\TemplateFileIsInvalid('DummyTemplate'),
        );

        $this->subject->getTemplate();
    }

    #[Framework\Attributes\Test]
    public function getTemplateThrowsExceptionIfTemplateFileCannotBeRead(): void
    {
        $this->expectException(Src\Exception\TemplateFileIsInvalid::class);

        $this->subject->getTemplate(new Tests\Unit\Fixtures\Classes\Renderer\Template\DummyTemplateResolver());
    }

    #[Framework\Attributes\Test]
    public function getTemplateReturnsTemplateFromResolvedTemplatePath(): void
    {
        self::assertStringEqualsFile(
            $this->templateRootPath . '/DummyTemplate.hbs',
            $this->subject->getTemplate($this->getTemplateResolver()),
        );
    }

    #[Framework\Attributes\Test]
    public function getTemplateReturnsTemplateFromResolvedTemplatePathWithConfiguredFormat(): void
    {
        $this->subject->setFormat('html');

        self::assertStringEqualsFile(
            $this->templateRootPath . '/DummyTemplate.html',
            $this->subject->getTemplate($this->getTemplateResolver()),
        );
    }

    #[Framework\Attributes\Test]
    public function getTemplateReturnsTemplateFromConfiguredTemplatePath(): void
    {
        $templatePath = $this->templateRootPath . '/DummyTemplate.hbs';

        $this->subject->setTemplatePath($templatePath);

        self::assertStringEqualsFile(
            $templatePath,
            $this->subject->getTemplate(),
        );
    }

    #[Framework\Attributes\Test]
    public function getTemplateReturnsTemplateFromConfiguredTemplatePathAndFormat(): void
    {
        $templatePath = $this->templateRootPath . '/DummyTemplate';

        $this->subject->setTemplatePath($templatePath);
        $this->subject->setFormat('html');

        self::assertStringEqualsFile(
            $templatePath . '.html',
            $this->subject->getTemplate(),
        );
    }

    #[Framework\Attributes\Test]
    public function assignAddsGivenVariableToConfiguredVariables(): void
    {
        $expected = [
            'foo' => 'baz',
            'baz' => 'foo',
        ];

        $this->subject->assign('baz', 'foo');

        self::assertSame($expected, $this->subject->getVariables());
    }

    #[Framework\Attributes\Test]
    public function assignMultipleAddsAllGivenVariablesToConfiguredVariables(): void
    {
        $expected = [
            'foo' => 'baz',
            'baz' => 'foo',
            'boo' => 'foo',
        ];

        $this->subject->assignMultiple([
            'baz' => 'foo',
            'boo' => 'foo',
        ]);

        self::assertSame($expected, $this->subject->getVariables());
    }
}
