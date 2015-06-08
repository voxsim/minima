<?php namespace Minima;

use Minima\Logging\Logger;
use Minima\Routing\Router;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;

class ApplicationFactory {
  public static function build($configuration = array()) {
    $defaultConfiguration = array(
			      'debug' => false,
			    );
    $configuration = array_merge($defaultConfiguration, $configuration);
    
    $logger = Logger::build($configuration);
    
    $dispatcher = new EventDispatcher();

    $router = new Router($configuration, $logger);
    
    $resolver = new ControllerResolver($logger);

    if(isset($configuration['debug']) && $configuration['debug'])
      return static::buildForDebug($configuration, $dispatcher, $resolver, $router);

    return static::buildForProduction($configuration, $dispatcher, $resolver, $router);
  }

  private static function buildForProduction($configuration, $dispatcher, $resolver, $router) {
    return new Application($configuration, $dispatcher, $resolver, $router);
  }

  private static function buildForDebug($configuration, $dispatcher, $resolver, $router) {
    return new ApplicationDebug($configuration, $dispatcher, $resolver, $router);
  }
}
