<?php

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/*
 * Routes of the Symfony bundles used during development.
 *
 * They are in PHP files since Symfony 7.3 and in XML files before, which do not exist any more in Symfony 8.
 */
return static function (RoutingConfigurator $routes): void {
    $file = static function (string $bundleClass, string $name): string {
        $directory = dirname((new \ReflectionClass($bundleClass))->getFileName()).'/Resources/config/routing/';

        return $directory.$name.(is_file($directory.$name.'.php') ? '.php' : '.xml');
    };

    $routes->import($file(WebProfilerBundle::class, 'wdt'))->prefix('/_wdt');
    $routes->import($file(WebProfilerBundle::class, 'profiler'))->prefix('/_profiler');
    $routes->import($file(FrameworkBundle::class, 'errors'))->prefix('/_error');
};
