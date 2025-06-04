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

use Fr\Typo3Handlebars\Exception;

/**
 * TemplatePathAwareConfigurationTrait
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
trait TemplatePathAwareConfigurationTrait
{
    protected function getTemplatePathFromConfiguration(): string
    {
        if (!isset($this->configuration['userFunc.']['templatePath'])) {
            throw new Exception\InvalidTemplateFileException(
                'Missing or invalid template path in configuration array.',
                1641990786
            );
        }

        return (string)$this->configuration['userFunc.']['templatePath'];
    }
}
