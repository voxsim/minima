<?php

namespace Minima\Controller;

use Minima\Kernel\NullHttpKernel;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ControllerResolver implements ControllerResolverInterface
{
    protected $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function resolve(Request $request, $type)
    {
	$controller = $this->getController($request);

        $event = new FilterControllerEvent(new NullHttpKernel(), $controller, $request, $type);
        $this->dispatcher->dispatch(KernelEvents::CONTROLLER, $event);
        $controller = $event->getController();

        $arguments = $this->getArguments($request, $controller);
	
	return array($controller, $arguments);
    }

    private function getController(Request $request)
    {
        if (!$controller = $request->attributes->get('_controller')) {
           throw new NotFoundHttpException(sprintf('Unable to find the controller for path "%s". The route is wrongly configured.', $request->getPathInfo()));
        }

        if (is_array($controller)) {
	  return $controller;
        }

        if (is_object($controller)) {
	  if (method_exists($controller, '__invoke')) {
	    return $controller;
	  }

	  throw new \InvalidArgumentException(sprintf('Controller "%s" for URI "%s" is not callable.', get_class($controller), $request->getPathInfo()));
        }

        if (false === strpos($controller, ':')) {
	  if (method_exists($controller, '__invoke')) {
	    return $this->instantiateController($controller);
	  } elseif (function_exists($controller)) {
	    return $controller;
	  }
        }

        $callable = $this->createController($controller);

        if (!is_callable($callable)) {
            throw new \InvalidArgumentException(sprintf('Controller "%s" for URI "%s" is not callable.', $controller, $request->getPathInfo()));
        }

        return $callable;
    }

    private function getArguments(Request $request, $controller)
    {
        if (is_array($controller)) {
            $r = new \ReflectionMethod($controller[0], $controller[1]);
        } elseif (is_object($controller) && !$controller instanceof \Closure) {
            $r = new \ReflectionObject($controller);
            $r = $r->getMethod('__invoke');
        } else {
            $r = new \ReflectionFunction($controller);
        }

        return $this->doGetArguments($request, $controller, $r->getParameters());
    }

    protected function doGetArguments(Request $request, $controller, array $parameters)
    {
      $attributes = $request->attributes->all();
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

    protected function createController($controller)
    {
        if (false === strpos($controller, '::')) {
            throw new \InvalidArgumentException(sprintf('Unable to find controller "%s".', $controller));
        }

        list($class, $method) = explode('::', $controller, 2);

        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        return array($this->instantiateController($class), $method);
    }

    protected function instantiateController($class)
    {
        return new $class();
    }
}
