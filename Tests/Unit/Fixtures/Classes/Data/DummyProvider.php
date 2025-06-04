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

namespace Fr\Typo3Handlebars\Tests\Unit\Fixtures\Classes\Data;

use Fr\Typo3Handlebars\Data;

/**
 * DummyProvider
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 */
final class DummyProvider implements Data\DataProvider
{
    /**
     * @var array<string, mixed>
     */
    public array $expectedData = [];

    public function get(array $data): Data\Response\ProviderResponse
    {
        return new Data\Response\SimpleProviderResponse($this->expectedData);
    }
}
