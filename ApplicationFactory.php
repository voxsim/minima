<?php

use Minima\Builder\LoggerBuilder;
use Minima\Controller\ControllerResolver;
use Minima\Controller\RequestControllerResolver;
use Minima\Kernel\HttpKernel;
use Minima\Http\ResponseMaker;
use Minima\Listener\ExceptionListener;
use Minima\Listener\LogListener;
use Minima\Listener\StringToResponseListener;
use Minima\Routing\Router;
use Minima\Security\Firewall;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\EventListener\ResponseListener;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;

class ApplicationFactory
{
    public static function build(EventDispatcherInterface $dispatcher, RouteCollection $routeCollection, $configuration = array())
    {
        $defaultConfiguration = array(
                  'debug' => false,
                  'charset' => 'UTF-8',
                  'security.firewalls' => array()
                );
        $configuration = array_merge($defaultConfiguration, $configuration);

        $logger = LoggerBuilder::build($configuration);
        $matcher = new UrlMatcher($routeCollection, new RequestContext());
        $router = new Router($matcher, $logger);
        $controllerResolver = new ControllerResolver();
        $resolver = new RequestControllerResolver($dispatcher, $controllerResolver);
        $responseMaker = new ResponseMaker();

        if (isset($configuration['debug']) && $configuration['debug']) {
            return static::buildForDebug($configuration, $dispatcher, $resolver, $router, $logger, $responseMaker, $controllerResolver);
        }

        return static::buildForProduction($configuration, $dispatcher, $resolver, $router, $logger, $responseMaker, $controllerResolver);
    }

    private static function buildForProduction($configuration, $dispatcher, $resolver, $router, $logger, $responseMaker, $controllerResolver)
    {
        $dispatcher->addSubscriber(new ExceptionListener($responseMaker));

        return static::buildForDebug($configuration, $dispatcher, $resolver, $router, $logger, $responseMaker, $controllerResolver);
    }

    private static function buildForDebug($configuration, $dispatcher, $resolver, $router, $logger, $responseMaker, $controllerResolver)
    {
        $dispatcher->addSubscriber(new Firewall($configuration['security.firewalls'], $controllerResolver));
        $dispatcher->addSubscriber(new LogListener($logger));
        $dispatcher->addSubscriber(new ResponseListener($configuration['charset']));
        $dispatcher->addSubscriber(new StringToResponseListener($responseMaker));

        return new HttpKernel($dispatcher, $resolver, new RequestStack(), $router);
    }
}
