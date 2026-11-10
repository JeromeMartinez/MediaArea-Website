<?php

// The source code follows PSR-1 and PSR-2 (the default rules of PHP-CS-Fixer are now the ones of PSR-12)
return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR1' => true,
        '@PSR2' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__.'/src')
    );
