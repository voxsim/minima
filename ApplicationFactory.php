<?php

use Minima\Builder\LoggerBuilder;
use Minima\Controller\ControllerResolver;
use Minima\Kernel\HttpKernel;
use Minima\Http\ResponseMaker;
use Minima\Listener\ExceptionListener;
use Minima\Listener\LogListener;
use Minima\Listener\StringToResponseListener;
use Minima\Routing\Router;
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
                  'charset' => 'UTF-8'
                );
        $configuration = array_merge($defaultConfiguration, $configuration);

        $logger = LoggerBuilder::build($configuration);
        $matcher = new UrlMatcher($routeCollection, new RequestContext());
        $router = new Router($matcher, $logger);
        $resolver = new ControllerResolver($dispatcher);
        $responseMaker = new ResponseMaker();

        if (isset($configuration['debug']) && $configuration['debug']) {
            return static::buildForDebug($configuration, $dispatcher, $resolver, $router, $logger, $responseMaker);
        }

        return static::buildForProduction($configuration, $dispatcher, $resolver, $router, $logger, $responseMaker);
    }

    private static function buildForProduction($configuration, $dispatcher, $resolver, $router, $logger, $responseMaker)
    {
        $dispatcher->addSubscriber(new ExceptionListener($responseMaker));

        return static::buildForDebug($configuration, $dispatcher, $resolver, $router, $logger, $responseMaker);
    }

    private static function buildForDebug($configuration, $dispatcher, $resolver, $router, $logger, $responseMaker)
    {
        $defaultConfiguration = array('charset' => 'UTF-8');
        $configuration = array_merge($defaultConfiguration, $configuration);

        $dispatcher->addSubscriber(new LogListener($logger));
        $dispatcher->addSubscriber(new ResponseListener($configuration['charset']));
        $dispatcher->addSubscriber(new StringToResponseListener($responseMaker));

        return new HttpKernel($dispatcher, $resolver, new RequestStack(), $router);
    }
}
