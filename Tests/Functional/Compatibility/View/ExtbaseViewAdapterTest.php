<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2021 Elias Häußler <e.haeussler@familie-redlich.de>
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

namespace Fr\Typo3Handlebars\Tests\Functional\Compatibility\View;

use Fr\Typo3Handlebars as Src;
use Fr\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Fluid;
use TYPO3\TestingFramework;

/**
 * ExtbaseViewAdapterTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Compatibility\View\ExtbaseViewAdapter::class)]
final class ExtbaseViewAdapterTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    protected bool $initializeDatabase = false;

    private Extbase\Mvc\Request $request;
    private Src\Compatibility\View\ExtbaseViewAdapter $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $extbaseRequestParameters = new Extbase\Mvc\ExtbaseRequestParameters(Tests\Unit\Fixtures\Classes\DummyController::class);
        $extbaseRequestParameters->setControllerAliasToClassNameMapping(['Dummy' => Tests\Unit\Fixtures\Classes\DummyController::class]);
        $extbaseRequestParameters->setControllerName('Dummy');
        $serverRequest = new Core\Http\ServerRequest('https://typo3-testing.local/');
        $serverRequest = $serverRequest->withAttribute('extbase', $extbaseRequestParameters);

        $this->request = new Extbase\Mvc\Request($serverRequest);
        $this->subject = new Src\Compatibility\View\ExtbaseViewAdapter(
            new Tests\Unit\Fixtures\Classes\DataProcessing\LogProcessor(),
        );

        $renderingContext = $this->subject->getRenderingContext();
        self::assertInstanceOf(Fluid\Core\Rendering\RenderingContext::class, $renderingContext);
        $renderingContext->setRequest($this->request);
    }

    #[Framework\Attributes\Test]
    public function assignAddsVariableToViewConfiguration(): void
    {
        $this->subject->assign('foo', 'baz');

        $expected = [
            'foo' => 'baz',
        ];

        $actual = unserialize($this->subject->render());

        self::assertSame($expected, $actual['configuration']['extbaseViewConfiguration']['variables']);
    }

    #[Framework\Attributes\Test]
    public function assignMultipleAddsVariablesToViewConfiguration(): void
    {
        $this->subject->assignMultiple(['foo' => 'foo', 'baz' => 'baz']);

        $expected = [
            'foo' => 'foo',
            'baz' => 'baz',
        ];

        $actual = unserialize($this->subject->render());

        self::assertSame($expected, $actual['configuration']['extbaseViewConfiguration']['variables']);
    }

    #[Framework\Attributes\Test]
    public function renderPassesAllRelevantConfigurationToTheProcessor(): void
    {
        $this->subject->assign('foo', 'baz');

        $expected = [
            'content' => '',
            'configuration' => [
                'extbaseViewConfiguration' => [
                    'controller' => Tests\Unit\Fixtures\Classes\DummyController::class,
                    'action' => 'dummy',
                    'request' => $this->request,
                    'variables' => [
                        'foo' => 'baz',
                    ],
                ],
            ],
        ];

        $actual = $this->subject->render('dummy');

        // Reset request body
        if (method_exists($this->request, 'getBody')) {
            $this->request->getBody()->close();
        }

        self::assertEquals($expected, unserialize($actual));
    }
}
