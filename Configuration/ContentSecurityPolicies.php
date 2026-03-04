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

use TYPO3\CMS\Core;

return Core\Type\Map::fromEntries([
    Core\Security\ContentSecurityPolicy\Scope::frontend(),
    new Core\Security\ContentSecurityPolicy\MutationCollection(
        // Required to properly render styles in {{varDump}} helper
        new Core\Security\ContentSecurityPolicy\Mutation(
            Core\Security\ContentSecurityPolicy\MutationMode::Extend,
            Core\Security\ContentSecurityPolicy\Directive::StyleSrc,
            Core\Security\ContentSecurityPolicy\SourceKeyword::nonceProxy,
        ),
    ),
]);
