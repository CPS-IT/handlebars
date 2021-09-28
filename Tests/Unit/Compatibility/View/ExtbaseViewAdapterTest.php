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

namespace Fr\Typo3Handlebars\Tests\Unit\Compatibility\View;

use Fr\Typo3Handlebars\Compatibility\View\ExtbaseViewAdapter;
use Fr\Typo3Handlebars\Tests\Unit\Fixtures\Classes\DataProcessing\LogProcessor;
use Fr\Typo3Handlebars\Tests\Unit\Fixtures\Classes\DummyController;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * ExtbaseViewAdapterTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
class ExtbaseViewAdapterTest extends UnitTestCase
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var LogProcessor
     */
    protected $processor;

    /**
     * @var ExtbaseViewAdapter
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = new Request(DummyController::class);
        $this->request->setControllerActionName('dummy');

        $controllerContext = new ControllerContext();
        $controllerContext->setRequest($this->request);

        $this->processor = new LogProcessor();
        $this->subject = new ExtbaseViewAdapter($this->processor);
        $this->subject->setControllerContext($controllerContext);
    }

    /**
     * @test
     */
    public function assignAddsVariableToViewConfiguration(): void
    {
        $this->subject->assign('foo', 'baz');

        $expected = [
            'foo' => 'baz',
        ];

        $actual = unserialize($this->subject->render());

        self::assertSame($expected, $actual['configuration']['extbaseViewConfiguration']['variables']);
    }

    /**
     * @test
     */
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

    /**
     * @test
     */
    public function canRenderAlwaysReturnsTrue(): void
    {
        self::assertTrue($this->subject->canRender(new ControllerContext()));
    }

    /**
     * @test
     */
    public function renderPassesAllRelevantConfigurationToTheProcessor(): void
    {
        $this->subject->assign('foo', 'baz');

        $expected = [
            'content' => '',
            'configuration' => [
                'extbaseViewConfiguration' => [
                    'controller' => DummyController::class,
                    'action' => 'dummy',
                    'request' => $this->request,
                    'variables' => [
                        'foo' => 'baz',
                    ],
                ],
            ],
        ];

        $actual = $this->subject->render();

        // Reset request body for TYPO3 v11
        if (method_exists($this->request, 'getBody')) {
            $this->request->getBody()->close();
        }

        self::assertEquals($expected, unserialize($actual));
    }

    /**
     * @test
     */
    public function initializeViewDoesNothing(): void
    {
        $this->subject->initializeView();
    }
}
