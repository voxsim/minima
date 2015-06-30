<?php

namespace Minima\Controller;

use Minima\Event\FilterControllerEvent;
use Minima\Util\Stringify;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RequestControllerResolver extends ControllerResolver
{
    protected $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function resolve($object)
    {
        try
        {
            $request = $object;

            $controller = $request->attributes->get('_controller');

            if (!$controller) {
                throw new NotFoundHttpException(sprintf('Unable to find the controller for path "%s". The route is wrongly configured.', $request->getPathInfo()));
            }

            $controller = $this->getController($controller);

            $event = new FilterControllerEvent($controller, $request);
            $this->dispatcher->dispatch(KernelEvents::CONTROLLER, $event);
            $controller = $event->getController();

            $attributes = $request->attributes->all();
            $attributes['request'] = $request;

            $arguments = $this->getArguments($controller, $attributes);

            return array($controller, $arguments);
        } catch(ControllerNotCallableException $exception) {
            throw new \InvalidArgumentException(sprintf('Controller "%s" for URI "%s" is not callable.', Stringify::varToString($controller), $request->getPathInfo()));
        }
    }
}
