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

// Namespace and indentation are intended as they must match with HelperRegistry class

namespace CPSIT\Typo3Handlebars\Renderer\Helper
{
    use DevTheorem\Handlebars;

    /**
     * @return \Closure(mixed..., Handlebars\HelperOptions): mixed
     */
    function mapExpectedCallable(callable $function): \Closure
    {
        return static function () use ($function) {
            $arguments = \func_get_args();
            /** @var Handlebars\HelperOptions $options */
            $options = \array_pop($arguments);

            return $function($options, ...$arguments);
        };
    }
}
