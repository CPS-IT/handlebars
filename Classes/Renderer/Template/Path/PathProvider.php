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

namespace CPSIT\Typo3Handlebars\Renderer\Template\Path;

use Symfony\Component\DependencyInjection;

/**
 * PathProvider
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[DependencyInjection\Attribute\AutoconfigureTag('handlebars.template_path_provider')]
interface PathProvider
{
    public const PARTIALS = 'partialRootPaths';
    public const TEMPLATES = 'templateRootPaths';

    /**
     * @return array<int, string>
     */
    public function getPartialRootPaths(): array;

    /**
     * @return array<int, string>
     */
    public function getTemplateRootPaths(): array;

    public function isCacheable(): bool;

    public static function getPriority(): int;
}
