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

namespace CPSIT\Typo3Handlebars\Tests\Unit\Renderer\Helper;

use CPSIT\Typo3Handlebars as Src;
use CPSIT\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * GetHelperTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Renderer\Helper\GetHelper::class)]
final class GetHelperTest extends TestingFramework\Core\Unit\UnitTestCase
{
    use Tests\HandlebarsTemplateTestTrait;

    private Src\Renderer\Helper\GetHelper $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\Renderer\Helper\GetHelper();
    }

    #[Framework\Attributes\Test]
    public function helperCanBeUsedInTemplate(): void
    {
        $context = new Src\Renderer\RenderingContext();
        $context->assign('foo', 'baz');

        self::assertRenderedTemplateEqualsString(
            '{{get context "variables[foo]"}}',
            'baz',
            [
                'context' => $context,
            ],
            [
                'get' => $this->subject->render(...),
            ],
        );
    }

    #[Framework\Attributes\Test]
    public function helperThrowsExceptionIfGivenSubjectIsUnsupported(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\TypeIsNotSupported(['object', 'array'], null),
        );

        self::renderTemplate(
            '{{get context "template"}}',
            [
                'context' => null,
            ],
            [
                'get' => $this->subject->render(...),
            ],
        );
    }

    #[Framework\Attributes\Test]
    public function helperThrowsExceptionIfGivenNameIsUnsupported(): void
    {
        $context = new Src\Renderer\RenderingContext();

        $this->expectExceptionObject(
            new Src\Exception\TypeIsNotSupported('string', null),
        );

        self::renderTemplate(
            '{{get context}}',
            [
                'context' => $context,
            ],
            [
                'get' => $this->subject->render(...),
            ],
        );
    }
}
