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

namespace Fr\Typo3Handlebars\Exception;

/**
 * InvalidTemplateFileException
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @codeCoverageIgnore
 */
final class InvalidTemplateFileException extends \RuntimeException
{
    public function __construct(
        private readonly string $templateFile = '',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        $message = \sprintf('The requested template file "%s" is invalid or could not be read.', $this->templateFile);
        parent::__construct($message, $code, $previous);
    }

    public function getTemplateFile(): string
    {
        return $this->templateFile;
    }
}
