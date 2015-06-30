<?php

namespace Minima\Security;

use Minima\Controller\ControllerResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RequestMatcher;

class Firewall implements EventSubscriberInterface
{
    private $controllerResolver;
    private $firewalls;

    public function __construct(array $firewalls, ControllerResolverInterface $controllerResolver) {
        $this->controllerResolver = $controllerResolver;
        $this->firewalls = $firewalls;

        if(!empty($this->firewalls)) {
            foreach($this->firewalls as $name => $firewall) {
                $this->firewalls[$name]['pattern'] = new RequestMatcher($firewall['pattern']);
            }
        }
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        foreach($this->firewalls as $name => $firewall) {
            if(isset($firewall['pattern']) && $firewall['pattern']->matches($request)) {
                $firewall['request'] = $request;

                $controller = $this->controllerResolver->getController($firewall['_controller']);
                $arguments = $this->controllerResolver->getArguments($controller, $firewall);

                $response = call_user_func_array($controller, $arguments);

                if(null !== $response)
                    $event->setResponse($response);
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onKernelRequest', -128),
        );
    }
}
