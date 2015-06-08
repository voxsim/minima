<?php namespace Minima;

use Minima\Logging\Logger;
use Minima\Routing\Router;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Routing\RouteCollection;

class ApplicationFactory {
  public static function build(EventDispatcher $dispatcher, RouteCollection $routeCollection, $configuration = array()) {
    $defaultConfiguration = array(
			      'debug' => false,
			    );
    $configuration = array_merge($defaultConfiguration, $configuration);
    
    $logger = Logger::build($configuration);
    
    $router = new Router($configuration, $routeCollection, $logger);
    
    $resolver = new ControllerResolver($logger);

    if(isset($configuration['debug']) && $configuration['debug'])
      return static::buildForDebug($configuration, $dispatcher, $resolver, $router, $logger);

    return static::buildForProduction($configuration, $dispatcher, $resolver, $router, $logger);
  }

  private static function buildForProduction($configuration, $dispatcher, $resolver, $router, $logger) {
    return new Application($configuration, $dispatcher, $resolver, $router, $logger);
  }

  private static function buildForDebug($configuration, $dispatcher, $resolver, $router, $logger) {
    return new ApplicationDebug($configuration, $dispatcher, $resolver, $router, $logger);
  }
}
