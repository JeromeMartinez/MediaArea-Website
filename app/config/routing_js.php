<?php

use FOS\JsRoutingBundle\FOSJsRoutingBundle;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/*
 * Routes of FOSJsRoutingBundle.
 *
 * They are in a PHP file since version 3.6 of the bundle and in a XML file before, which can not be loaded
 * any more with Symfony 8.
 */
return static function (RoutingConfigurator $routes): void {
    $directory = dirname((new \ReflectionClass(FOSJsRoutingBundle::class))->getFileName()).'/Resources/config/routing/';

    $routes->import($directory.(is_file($directory.'routing.php') ? 'routing.php' : 'routing-sf4.xml'));
};
