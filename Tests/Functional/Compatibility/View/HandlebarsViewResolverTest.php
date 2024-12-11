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
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Core;
use TYPO3\CMS\Frontend;
use TYPO3\TestingFramework;

/**
 * HandlebarsViewResolverTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Compatibility\View\HandlebarsViewResolver::class)]
final class HandlebarsViewResolverTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    protected bool $initializeDatabase = false;

    private Tests\Unit\Fixtures\Classes\DataProcessing\DummyProcessor $processor;
    private Src\Compatibility\View\HandlebarsViewResolver $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $container = new DependencyInjection\Container();
        $container->set('foo', new Tests\Unit\Fixtures\Classes\DummyView());

        $this->processor = new Tests\Unit\Fixtures\Classes\DataProcessing\DummyProcessor();
        $this->subject = new Src\Compatibility\View\HandlebarsViewResolver($container);
        $this->subject->setDefaultViewClass('foo');

        /* @phpstan-ignore argument.type */
        $this->subject->setProcessorMap([
            'FooController' => [
                '_all' => $this->processor,
                'foo' => $this->processor,
            ],
            'BazController' => [
                'baz' => $this->processor,
            ],
        ]);

        $serverRequest = new Core\Http\ServerRequest('https://typo3-testing.local/');
        $serverRequest = $serverRequest->withAttribute('applicationType', Core\Core\SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $serverRequest = $serverRequest->withAttribute('currentContentObject', new Frontend\ContentObject\ContentObjectRenderer());

        $GLOBALS['TYPO3_REQUEST'] = $serverRequest;
    }

    #[Framework\Attributes\Test]
    public function resolveReturnsViewFromDefaultResolverIfControllerIsNotSupported(): void
    {
        self::assertNotInstanceOf(
            Src\Compatibility\View\ExtbaseViewAdapter::class,
            $this->subject->resolve('UnsupportedController', 'foo', 'html')
        );
    }

    #[Framework\Attributes\Test]
    public function resolveReturnsViewFromDefaultResolverIfControllerActionIsNotSupported(): void
    {
        self::assertNotInstanceOf(
            Src\Compatibility\View\ExtbaseViewAdapter::class,
            $this->subject->resolve('BazController', 'foo', 'html')
        );
    }

    #[Framework\Attributes\Test]
    public function resolveReturnsExtbaseViewAdapterForSupportedControllerAndSpecificAction(): void
    {
        self::assertInstanceOf(
            Src\Compatibility\View\ExtbaseViewAdapter::class,
            $this->subject->resolve('FooController', 'foo', 'html'),
        );

        unset($GLOBALS['TYPO3_REQUEST']);
    }

    #[Framework\Attributes\Test]
    public function resolveReturnsExtbaseViewAdapterForSupportedControllerAndGeneralAction(): void
    {
        self::assertInstanceOf(
            Src\Compatibility\View\ExtbaseViewAdapter::class,
            $this->subject->resolve('FooController', 'baz', 'html'),
        );
    }

    #[Framework\Attributes\Test]
    public function resolvePassesContentObjectRendererToResolvedProcessor(): void
    {
        self::assertNull($this->processor->getContentObjectRenderer());

        $this->subject->resolve('FooController', 'foo', 'html');

        self::assertInstanceOf(
            Frontend\ContentObject\ContentObjectRenderer::class,
            $this->processor->getContentObjectRenderer(),
        );
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TYPO3_REQUEST']);

        parent::tearDown();
    }
}
