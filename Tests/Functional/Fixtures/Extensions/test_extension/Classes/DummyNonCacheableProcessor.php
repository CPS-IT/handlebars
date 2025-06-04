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

namespace Fr\Typo3Handlebars\TestExtension;

use Fr\Typo3Handlebars\DataProcessing;

/**
 * DummyNonCacheableProcessor
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final class DummyNonCacheableProcessor extends DataProcessing\AbstractDataProcessor
{
    use DefaultContextAwareConfigurationTrait;
    use TemplatePathAwareConfigurationTrait;

    protected function render(): string
    {
        return json_encode([
            'templatePath' => $this->getTemplatePathFromConfiguration(),
            'context' => $this->getDefaultContextFromConfiguration(),
        ], JSON_THROW_ON_ERROR);
    }
}
