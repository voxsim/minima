<?php namespace Minima;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Routing;
use Symfony\Component\HttpKernel;
use \Minima\Logging\Logger;

class ApplicationFactory {
  public static function build($configuration = array()) {
    $defaultConfiguration = array(
			      'debug' => false,
			      'log.level' => 'debug',
			      'log.file' => __DIR__ . '/../minima.log'
			    );
    $configuration = array_merge($defaultConfiguration, $configuration);
    $dispatcher = new EventDispatcher();
    $resolver = new ControllerResolver();

    $logger = new Logger($configuration);
    $dispatcher->addSubscriber(new \Minima\Logging\LogListener(new \Minima\Logging\Logger($configuration)));
    
    $routes = new \Minima\Routing\ApplicationRouteCollection($configuration);
    $context = new Routing\RequestContext();
    $matcher = new Routing\Matcher\UrlMatcher($routes, $context);
    $dispatcher->addSubscriber(new HttpKernel\EventListener\RouterListener($matcher));

    if(isset($configuration['debug']) && $configuration['debug'])
      return static::buildForDebug($configuration, $dispatcher, $resolver);

    return static::buildForProduction($configuration, $dispatcher, $resolver);
  }

  private static function buildForProduction($configuration, $dispatcher, $resolver) {
    return new Application($configuration, $dispatcher, $resolver);
  }

  private static function buildForDebug($configuration, $dispatcher, $resolver) {
    return new ApplicationDebug($configuration, $dispatcher, $resolver);
  }
}
