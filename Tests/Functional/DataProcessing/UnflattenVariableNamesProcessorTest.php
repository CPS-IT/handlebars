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

namespace CPSIT\Typo3Handlebars\Tests\Functional\DataProcessing;

use CPSIT\Typo3Handlebars as Src;
use PHPUnit\Framework;
use TYPO3\CMS\Frontend;
use TYPO3\TestingFramework;

/**
 * UnflattenVariableNamesProcessorTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\DataProcessing\UnflattenVariableNamesProcessor::class)]
final class UnflattenVariableNamesProcessorTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    private Src\DataProcessing\UnflattenVariableNamesProcessor $subject;

    public function setUp(): void
    {
        parent::setUp();

        $this->subject = new Src\DataProcessing\UnflattenVariableNamesProcessor();
    }

    #[Framework\Attributes\Test]
    public function processUnflattensVariableNamesFromProcessedData(): void
    {
        $expected = [
            'foo' => [
                'baz' => 'boo',
            ],
        ];

        $actual = $this->subject->process(
            $this->createMock(Frontend\ContentObject\ContentObjectRenderer::class),
            [],
            [],
            [
                'foo.baz' => 'boo',
            ],
        );

        self::assertSame($expected, $actual);
    }
}
