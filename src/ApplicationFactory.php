<?php namespace Minima;

use Minima\Cache\SetTtlListener;
use Minima\Controller\ControllerResolver;
use Minima\Kernel\HttpKernel;
use Minima\Logging\Logger;
use Minima\Logging\LogListener;
use Minima\Routing\Router;
use Minima\Routing\StringToResponseListener;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Exception\FlattenException;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener;
use Symfony\Component\HttpKernel\EventListener\ResponseListener;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;
use Symfony\Component\HttpKernel\HttpCache\Store;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouteCollection;

class ApplicationFactory {
  public static function build(EventDispatcher $dispatcher, RouteCollection $routeCollection, $configuration = array()) {
    $defaultConfiguration = array(
			      'debug' => false,
			    );
    $configuration = array_merge($defaultConfiguration, $configuration);
    
    $logger = Logger::build($configuration);
    $router = new Router($configuration, $routeCollection, $logger);
    $resolver = new ControllerResolver($dispatcher, $logger);
    if(isset($configuration['debug']) && $configuration['debug'])
      return static::buildForDebug($configuration, $dispatcher, $resolver, $router, $logger);

    return static::buildForProduction($configuration, $dispatcher, $resolver, $router, $logger);
  }

  private static function buildForProduction($configuration, $dispatcher, $resolver, $router, $logger) {
    $defaultConfiguration = array(
			      'cache.path' =>  __DIR__.'/../cache',
			      'cache.page' => 10,
			    );
    $configuration = array_merge($defaultConfiguration, $configuration);

    $httpKernel = static::buildForDebug($configuration, $dispatcher, $resolver, $router, $logger);

    $errorHandler = function (FlattenException $exception) {
      $msg = 'Something went wrong! ('.$exception->getMessage().')';
   
      return new Response($msg, $exception->getStatusCode());
    };
    $dispatcher->addSubscriber(new ExceptionListener($errorHandler));
    $dispatcher->addSubscriber(new SetTtlListener($configuration['cache.page']));

    return new HttpCache($httpKernel, new Store($configuration['cache.path']));
  }

  private static function buildForDebug($configuration, $dispatcher, $resolver, $router, $logger) {
    $defaultConfiguration = array('charset' => 'UTF-8');
    $configuration = array_merge($defaultConfiguration, $configuration);
    
    $dispatcher->addSubscriber(new LogListener($logger));
    $dispatcher->addSubscriber(new ResponseListener($configuration['charset']));
    $dispatcher->addSubscriber(new StringToResponseListener);

    return new HttpKernel($dispatcher, $resolver, new RequestStack(), $router);
  }
}
