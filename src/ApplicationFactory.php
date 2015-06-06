<?php namespace Minima;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;

class ApplicationFactory {
  public static function build($configuration = array()) {
    if(isset($configuration['debug']) && $configuration['debug']) {
      return static::buildForDebug();
    }

    return static::buildForProduction();
  }

  public static function buildForProduction($configuration = array()) {
    $dispatcher = new EventDispatcher();
    $resolver = new ControllerResolver();

    return new Application($configuration, $dispatcher, $resolver);
  }

  public static function buildForDebug($configuration = array()) {
    $dispatcher = new EventDispatcher();
    $resolver = new ControllerResolver();

    return new ApplicationDebug($configuration, $dispatcher, $resolver);
  }
}
