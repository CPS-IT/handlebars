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

namespace CPSIT\Typo3Handlebars\Tests;

use Psr\Http\Message;
use Symfony\Component\DependencyInjection;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Frontend;

/**
 * FrontendRequestTrait
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 * @internal
 */
trait FrontendRequestTrait
{
    /**
     * @param-out Frontend\Cache\CacheInstruction $cacheInstruction
     * @param-out Core\TypoScript\FrontendTypoScript $frontendTypoScript
     */
    protected function buildServerRequest(
        ?Frontend\Cache\CacheInstruction &$cacheInstruction = null,
        ?Core\TypoScript\FrontendTypoScript &$frontendTypoScript = null,
    ): Message\ServerRequestInterface {
        $cacheInstruction ??= new Frontend\Cache\CacheInstruction();

        if ($frontendTypoScript === null) {
            $astBuilder = new Core\TypoScript\AST\AstBuilder(new Core\EventDispatcher\NoopEventDispatcher());
            $factory = new Core\TypoScript\TypoScriptStringFactory(
                new DependencyInjection\Container(),
                new Core\TypoScript\Tokenizer\LossyTokenizer(),
            );
            $rootNode = $factory->parseFromString('', $astBuilder);

            $frontendTypoScript = new Core\TypoScript\FrontendTypoScript($rootNode, [], [], []);
            $frontendTypoScript->setSetupTree($rootNode);
            $frontendTypoScript->setSetupArray([]);
            $frontendTypoScript->setConfigArray([]);
        }

        $serverRequest = new Core\Http\ServerRequest('https://typo3-testing.local/');
        $serverRequest = $serverRequest
            ->withAttribute('applicationType', Core\Core\SystemEnvironmentBuilder::REQUESTTYPE_FE)
            ->withAttribute('frontend.cache.instruction', $cacheInstruction)
            ->withAttribute('frontend.typoscript', $frontendTypoScript)
        ;

        $GLOBALS['TYPO3_REQUEST'] = $serverRequest;

        return $serverRequest;
    }

    /**
     * @param-out Extbase\Mvc\ExtbaseRequestParameters $extbaseRequestParameters
     */
    protected function buildExtbaseRequest(?Extbase\Mvc\ExtbaseRequestParameters &$extbaseRequestParameters = null): Extbase\Mvc\Request
    {
        $serverRequest = $this->buildServerRequest();

        if ($extbaseRequestParameters === null) {
            $extbaseRequestParameters = new Extbase\Mvc\ExtbaseRequestParameters('Vendor\\Extension\\Controller\\FooController');
            $extbaseRequestParameters->setControllerActionName('baz');
        }

        $serverRequest = $serverRequest->withAttribute('extbase', $extbaseRequestParameters);

        $GLOBALS['TYPO3_REQUEST'] = $serverRequest;

        return new Extbase\Mvc\Request($serverRequest);
    }
}
