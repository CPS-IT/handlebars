<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * Copyright (C) 2020 Elias Häußler <e.haeussler@familie-redlich.de>
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

namespace Fr\Typo3Handlebars\Traits;

use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * HandlebarsHelperTrait
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
trait HandlebarsHelperTrait
{
    /**
     * @var array<string, callable>
     */
    protected $helpers = [];

    /**
     * @param string $name
     * @param mixed $function
     * @return self
     */
    public function registerHelper(string $name, $function): self
    {
        if (!$this->isValidHelper($function)) {
            if ($this->logger instanceof LoggerInterface) {
                $this->logger->critical(
                    'Error while registering Handlebars helper "' . $name . '".',
                    ['name' => $name, 'function' => $function]
                );
            }
            return $this;
        }
        $this->helpers[$name] = $this->resolveHelperFunction($function);
        return $this;
    }

    /**
     * @param mixed $function
     * @return callable
     */
    protected function resolveHelperFunction($function): callable
    {
        if (!is_string($function) || strpos($function, '::') === false) {
            return $function;
        }

        // Instantiate class and use combination of object and method name as callable Helper function
        /** @var class-string $className */
        [$className, $methodName] = explode('::', $function);
        $instance = GeneralUtility::makeInstance($className);

        return [$instance, $methodName];
    }

    /**
     * @param mixed $helperFunction
     * @return bool
     */
    protected function isValidHelper($helperFunction): bool
    {
        return is_callable($helperFunction);
    }
}
