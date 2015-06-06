<?php namespace Minima;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use \Minima\Logging\Logger;

class ApplicationFactory {
  public static function build($configuration = array()) {
    $defaultConfiguration = array(
			      'charset' => 'UTF-8',
			      'debug' => false,
			      'twig.path' => __DIR__.'/../views',
			      'cache.path' =>  __DIR__.'/../cache',
			      'cache.page' => 10,
			      'log.level' => 'debug',
			      'log.file' => __DIR__ . '/../minima.log'
			    );
    $configuration = array_merge($defaultConfiguration, $configuration);

    if(isset($configuration['debug']) && $configuration['debug']) {
      return static::buildForDebug($configuration);
    }

    return static::buildForProduction($configuration);
  }

  private static function buildForProduction($configuration) {
    $dispatcher = new EventDispatcher();
    $resolver = new ControllerResolver();
    $logger = new Logger($configuration);

    return new Application($configuration, $dispatcher, $resolver, $logger);
  }

  private static function buildForDebug($configuration) {
    $dispatcher = new EventDispatcher();
    $resolver = new ControllerResolver();
    $logger = new Logger($configuration);

    return new ApplicationDebug($configuration, $dispatcher, $resolver, $logger);
  }
}
