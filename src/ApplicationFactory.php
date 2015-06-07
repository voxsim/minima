<?php namespace Minima;

use Minima\Logging\Logger;
use Minima\Routing\Router;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;

class ApplicationFactory {
  public static function build($configuration = array()) {
    $defaultConfiguration = array(
			      'debug' => false,
			      'log.level' => 'debug',
			      'log.file' => __DIR__ . '/../minima.log'
			    );
    $configuration = array_merge($defaultConfiguration, $configuration);

    $dispatcher = new EventDispatcher();
    $dispatcher->addSubscriber(new Logger($configuration));
    
    $resolver = new ControllerResolver();
    $router = new Router($configuration);

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
