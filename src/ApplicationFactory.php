<?php namespace Minima;

use Minima\Builder\LoggerBuilder;
use Minima\Controller\ControllerResolver;
use Minima\Kernel\HttpKernel;
use Minima\Listener\ExceptionListener;
use Minima\Listener\LogListener;
use Minima\Listener\SetTtlListener;
use Minima\Listener\StringToResponseListener;
use Minima\Routing\Router;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\EventListener\ResponseListener;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;
use Symfony\Component\HttpKernel\HttpCache\Store;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;

class ApplicationFactory {
  public static function build(EventDispatcher $dispatcher, RouteCollection $routeCollection, $configuration = array()) {
    $defaultConfiguration = array(
			      'debug' => false,
			    );
    $configuration = array_merge($defaultConfiguration, $configuration);
    
    $logger = LoggerBuilder::build($configuration);
    $matcher = new UrlMatcher($routeCollection, new RequestContext());
    $router = new Router($matcher, $logger);
    $resolver = new ControllerResolver($dispatcher);
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

    $dispatcher->addSubscriber(new ExceptionListener());
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
