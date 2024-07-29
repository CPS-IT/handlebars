<?php

declare(strict_types=1);

$config = \TYPO3\CodingStandards\CsFixerConfig::create();
$config->setParallelConfig(\PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect());

$finder = $config->getFinder()
    ->in(__DIR__)
    ->ignoreVCSIgnored(true);

return $config;
