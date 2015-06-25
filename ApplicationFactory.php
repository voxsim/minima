<?php

use Minima\Builder\LoggerBuilder;
use Minima\Builder\FirewallMapBuilder;
use Minima\Controller\ControllerResolver;
use Minima\Kernel\HttpKernel;
use Minima\Listener\ExceptionListener;
use Minima\Listener\LogListener;
use Minima\Listener\StringToResponseListener;
use Minima\Routing\Router;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\EventListener\ResponseListener;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;
use Symfony\Component\HttpKernel\HttpCache\Store;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ApplicationFactory {
  public static function build(EventDispatcherInterface $dispatcher, RouteCollection $routeCollection, $configuration = array(), TokenStorageInterface $tokenStorage) {
    $defaultConfiguration = array(
			      'debug' => false,
			    );
    $configuration = array_merge($defaultConfiguration, $configuration);
    
    $logger = LoggerBuilder::build($configuration);
    $matcher = new UrlMatcher($routeCollection, new RequestContext());
    $router = new Router($matcher, $logger);
    $resolver = new ControllerResolver($dispatcher);

    if(isset($configuration['debug']) && $configuration['debug'])
      return static::buildForDebug($configuration, $dispatcher, $resolver, $router, $logger, $tokenStorage);

    return static::buildForProduction($configuration, $dispatcher, $resolver, $router, $logger, $tokenStorage);
  }

  private static function buildForProduction($configuration, $dispatcher, $resolver, $router, $logger, $tokenStorage) {
    $defaultConfiguration = array(
			      'cache.path' =>  __DIR__.'/../cache',
			      'cache.page' => 10,
			    );
    $configuration = array_merge($defaultConfiguration, $configuration);

    $httpKernel = static::buildForDebug($configuration, $dispatcher, $resolver, $router, $logger, $tokenStorage);

    $dispatcher->addSubscriber(new ExceptionListener());

    return new HttpCache($httpKernel, new Store($configuration['cache.path']), null, array('default_ttl' => $configuration['cache.page']));
  }

  private static function buildForDebug($configuration, $dispatcher, $resolver, $router, $logger, $tokenStorage) {
    $defaultConfiguration = array('charset' => 'UTF-8');
    $configuration = array_merge($defaultConfiguration, $configuration);
    
    $dispatcher->addSubscriber(new LogListener($logger));
    $dispatcher->addSubscriber(new ResponseListener($configuration['charset']));
    $dispatcher->addSubscriber(new StringToResponseListener);

    $firewall = FirewallMapBuilder::build($configuration, $logger, $tokenStorage, $dispatcher);
    $dispatcher->addSubscriber($firewall);

    return new HttpKernel($dispatcher, $resolver, new RequestStack(), $router);
  }
}
