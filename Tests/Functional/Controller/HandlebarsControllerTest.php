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

namespace CPSIT\Typo3Handlebars\Tests\Functional\Controller;

use CPSIT\Typo3Handlebars as Src;
use PHPUnit\Framework;
use TYPO3\TestingFramework;

/**
 * HandlebarsControllerTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Controller\HandlebarsController::class)]
final class HandlebarsControllerTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'handlebars',
        'test_extension',
    ];

    protected array $pathsToLinkInTestInstance = [
        'typo3conf/ext/handlebars/Tests/Build/sites' => 'typo3conf/sites',
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(dirname(__DIR__) . '/Fixtures/Database/pages.csv');
        $this->setUpFrontendRootPage(1, [
            'EXT:test_extension/Configuration/TypoScript/setup.typoscript',
        ]);
    }

    #[Framework\Attributes\Test]
    public function resolveViewUsesExtbaseHandlebarsViewResolverToResolveView(): void
    {
        $response = $this->executeFrontendSubRequest(
            new TestingFramework\Core\Functional\Framework\Frontend\InternalRequest('http://typo3-testing.local/'),
        );

        self::assertSame(
            'This is the default template, Foo!',
            \trim((string)$response->getBody()),
        );
    }

    #[Framework\Attributes\Test]
    public function renderViewRendersConfiguredExtbaseHandlebarsView(): void
    {
        $response = $this->executeFrontendSubRequest(
            new TestingFramework\Core\Functional\Framework\Frontend\InternalRequest('http://typo3-testing.local/subpage-1'),
        );

        self::assertSame(
            'This is the rendered template, Foo!',
            \trim((string)$response->getBody()),
        );
    }

    #[Framework\Attributes\Test]
    public function renderViewRendersExtbaseHandlebarsViewWithGivenTemplateName(): void
    {
        $response = $this->executeFrontendSubRequest(
            new TestingFramework\Core\Functional\Framework\Frontend\InternalRequest('http://typo3-testing.local/subpage-2'),
        );

        self::assertSame(
            'Hello World!',
            \trim((string)$response->getBody()),
        );
    }
}
