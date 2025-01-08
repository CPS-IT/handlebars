<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2025 Elias Häußler <e.haeussler@familie-redlich.de>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace Fr\Typo3Handlebars\Tests\Unit\Renderer\Template\View;

use Fr\Typo3Handlebars as Src;
use Fr\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * HandlebarsViewTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Template\View\HandlebarsView::class)]
final class HandlebarsViewTest extends TestingFramework\Core\Unit\UnitTestCase
{
    use Tests\HandlebarsTemplateResolverTrait;

    private Src\Renderer\Template\View\HandlebarsView $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\Renderer\Template\View\HandlebarsView(
            'DummyTemplate',
            [
                'foo' => 'baz',
            ],
        );
    }

    #[Framework\Attributes\Test]
    public function getTemplateThrowsExceptionIfNeitherTemplatePathNorTemplateSourceAreDefined(): void
    {
        $subject = new Src\Renderer\Template\View\HandlebarsView();

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
