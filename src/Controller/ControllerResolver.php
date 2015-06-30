<?php

namespace Minima\Controller;

use Minima\Event\FilterControllerEvent;
use Minima\Util\Stringify;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ControllerResolver implements ControllerResolverInterface
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

    private function getController($controller)
    {
        if (is_array($controller)) {
            return $controller;
        }

        if (is_object($controller)) {
            if (method_exists($controller, '__invoke')) {
                return $controller;
            }

            throw new ControllerNotCallableException;
        }

        if (false === strpos($controller, ':')) {
            if (method_exists($controller, '__invoke')) {
                return $this->instantiateController($controller);
            } elseif (function_exists($controller)) {
                return $controller;
            }
        }

        if (false === strpos($controller, '::')) {
            throw new \InvalidArgumentException(sprintf('Unable to find controller "%s".', $controller));
        }

        list($class, $method) = explode('::', $controller, 2);

        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        $callable = array($this->instantiateController($class), $method);

        if (!is_callable($callable)) {
            throw new ControllerNotCallableException;
        }

        return $callable;
    }

    private function getArguments($controller, array $attributes)
    {
        if (is_array($controller)) {
            $r = new \ReflectionMethod($controller[0], $controller[1]);
        } elseif (is_object($controller) && !$controller instanceof \Closure) {
            $r = new \ReflectionObject($controller);
            $r = $r->getMethod('__invoke');
        } else {
            $r = new \ReflectionFunction($controller);
        }

        return $this->doGetArguments($controller, $attributes, $r->getParameters());
    }

    protected function doGetArguments($controller, array $attributes, array $parameters)
    {
        $arguments = array();
        foreach ($parameters as $param) {
            if (array_key_exists($param->name, $attributes)) {
                $arguments[] = $attributes[$param->name];
            } elseif ($param->getClass() && $param->getClass()->isInstance($request)) {
                $arguments[] = $request;
            } elseif ($param->isDefaultValueAvailable()) {
                $arguments[] = $param->getDefaultValue();
            } else {
                if (is_array($controller)) {
                    $repr = sprintf('%s::%s()', get_class($controller[0]), $controller[1]);
                } elseif (is_object($controller)) {
                    $repr = get_class($controller);
                } else {
                    $repr = $controller;
                }

                throw new \RuntimeException(sprintf('Controller "%s" requires that you provide a value for the "$%s" argument (because there is no default value or because there is a non optional argument after this one).', $repr, $param->name));
            }
        }

        return $arguments;
    }

    protected function instantiateController($class)
    {
        return new $class();
    }
}
