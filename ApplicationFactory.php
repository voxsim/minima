<?php

use Minima\Provider\LoggerProvider;
use Minima\Controller\ControllerResolver;
use Minima\Controller\RequestControllerResolver;
use Minima\Kernel\HttpKernel;
use Minima\Http\ResponseMaker;
use Minima\Listener\ExceptionListener;
use Minima\Listener\LogListener;
use Minima\Listener\StringToResponseListener;
use Minima\FrontendController\FrontendControllerInterface;
use Minima\Security\Firewall;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\EventListener\ResponseListener;

class ApplicationFactory
{
    public static function build(array $configuration, EventDispatcherInterface $dispatcher, FrontendControllerInterface $frontendController)
    {
        $defaultConfiguration = array(
                  'root' => __DIR__,
                  'debug' => false,
                  'charset' => 'UTF-8',
                  'security.firewalls' => array()
                );
        $configuration = array_merge($defaultConfiguration, $configuration);

        if (isset($configuration['debug']) && $configuration['debug'])
            return static::buildForDebug($configuration, $dispatcher, $frontendController);

        return static::buildForProduction($configuration, $dispatcher, $frontendController);
    }

    private static function buildForProduction($configuration, $dispatcher, $frontendController)
    {
        $dispatcher->addSubscriber(new ExceptionListener());

        return static::buildForDebug($configuration, $dispatcher, $frontendController);
    }

    private static function buildForDebug($configuration, $dispatcher, $frontendController)
    {
        $controllerResolver = new ControllerResolver();
        $dispatcher->addSubscriber(new Firewall($configuration['security.firewalls'], $controllerResolver));

        $logger = LoggerProvider::build($configuration);
        $dispatcher->addSubscriber(new LogListener($logger));

        $dispatcher->addSubscriber(new ResponseListener($configuration['charset']));
        $dispatcher->addSubscriber(new StringToResponseListener());

        $resolver = new RequestControllerResolver($dispatcher, $controllerResolver);
        $requestStack = new RequestStack();
        return new HttpKernel($dispatcher, $resolver, $requestStack, $frontendController);
    }
}
