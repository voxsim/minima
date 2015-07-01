<?php

use Minima\Builder\LoggerBuilder;
use Minima\Controller\ControllerResolver;
use Minima\Controller\RequestControllerResolver;
use Minima\Kernel\HttpKernel;
use Minima\Http\ResponseMaker;
use Minima\Listener\ExceptionListener;
use Minima\Listener\LogListener;
use Minima\Listener\StringToResponseListener;
use Minima\Routing\RouterInterface;
use Minima\Security\Firewall;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\EventListener\ResponseListener;

class ApplicationFactory
{
    public static function build(array $configuration, EventDispatcherInterface $dispatcher, RouterInterface $router)
    {
        $defaultConfiguration = array(
                  'root' => __DIR__,
                  'debug' => false,
                  'charset' => 'UTF-8',
                  'security.firewalls' => array()
                );
        $configuration = array_merge($defaultConfiguration, $configuration);

        if (isset($configuration['debug']) && $configuration['debug'])
            return static::buildForDebug($configuration, $dispatcher, $router);

        return static::buildForProduction($configuration, $dispatcher, $router);
    }

    private static function buildForProduction($configuration, $dispatcher, $router)
    {
        $responseMaker = new ResponseMaker();
        $dispatcher->addSubscriber(new ExceptionListener($responseMaker));

        return static::buildForDebug($configuration, $dispatcher, $router);
    }

    private static function buildForDebug($configuration, $dispatcher, $router)
    {
        $controllerResolver = new ControllerResolver();
        $dispatcher->addSubscriber(new Firewall($configuration['security.firewalls'], $controllerResolver));

        $logger = LoggerBuilder::build($configuration);
        $dispatcher->addSubscriber(new LogListener($logger));

        $dispatcher->addSubscriber(new ResponseListener($configuration['charset']));

        $responseMaker = new ResponseMaker();
        $dispatcher->addSubscriber(new StringToResponseListener($responseMaker));

        $resolver = new RequestControllerResolver($dispatcher, $controllerResolver);
        $requestStack = new RequestStack();
        return new HttpKernel($dispatcher, $resolver, $requestStack, $router);
    }
}
