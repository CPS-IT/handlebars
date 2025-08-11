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
 * GlobalPathProvider
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
final readonly class GlobalPathProvider implements PathProvider
{
    /**
     * @var array<int, string>
     */
    private array $partialRootPaths;

    /**
     * @var array<int, string>
     */
    private array $templateRootPaths;

    /**
     * @param array{
     *     partialRootPaths: array<int, string>,
     *     templateRootPaths: array<int, string>,
     * } $viewConfiguration
     */
    public function __construct(
        #[DependencyInjection\Attribute\Autowire([
            self::PARTIALS => '%handlebars.partialRootPaths%',
            self::TEMPLATES => '%handlebars.templateRootPaths%',
        ])]
        array $viewConfiguration,
    ) {
        $this->partialRootPaths = $viewConfiguration[self::PARTIALS];
        $this->templateRootPaths = $viewConfiguration[self::TEMPLATES];
    }

    public function getPartialRootPaths(): array
    {
        return $this->partialRootPaths;
    }

    public function getTemplateRootPaths(): array
    {
        return $this->templateRootPaths;
    }

    public function isCacheable(): bool
    {
        return true;
    }

    public static function getPriority(): int
    {
        return 0;
    }
}
