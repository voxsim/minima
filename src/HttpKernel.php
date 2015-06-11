<?php
namespace Minima;

use Minima\Routing\RouterInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class HttpKernel implements HttpKernelInterface, TerminableInterface
{
  protected $dispatcher;
  protected $resolver;
  protected $requestStack;

  public function __construct(EventDispatcherInterface $dispatcher, ControllerResolverInterface $resolver, RequestStack $requestStack = null, RouterInterface $router = null)
  {
    $this->dispatcher = $dispatcher;
    $this->resolver = $resolver;
    $this->requestStack = $requestStack ?: new RequestStack();
    $this->router = $router;
  }

  public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
  {
    try {
      return $this->handleRaw($request, $type);
    } catch (\Exception $e) {
      if (false === $catch) {
	$this->finishRequest($request, $type);

	throw $e;
      }

      return $this->handleException($e, $request, $type);
    }
  }

  public function terminate(Request $request, Response $response)
  {
    $this->dispatcher->dispatch(KernelEvents::TERMINATE, new PostResponseEvent($this, $request, $response));
  }

  public function terminateWithException(\Exception $exception)
  {
      if (!$request = $this->requestStack->getMasterRequest()) {
	  throw new \LogicException('Request stack is empty', 0, $exception);
      }

      $response = $this->handleException($exception, $request, self::MASTER_REQUEST);

      $response->sendHeaders();
      $response->sendContent();

      $this->terminate($request, $response);
  }

  private function handleRaw(Request $request, $type = self::MASTER_REQUEST)
  {
    $this->requestStack->push($request);

    $event = new GetResponseEvent($this, $request, $type);
    $this->dispatcher->dispatch(KernelEvents::REQUEST, $event);

    if ($event->hasResponse()) {
      return $this->filterResponse($event->getResponse(), $request, $type);
    }

    if($this->router != null) {
      $this->router->lookup($request);
    }

    if (false === $controller = $this->resolver->getController($request)) {
      throw new NotFoundHttpException(sprintf('Unable to find the controller for path "%s". The route is wrongly configured.', $request->getPathInfo()));
    }

    $event = new FilterControllerEvent($this, $controller, $request, $type);
    $this->dispatcher->dispatch(KernelEvents::CONTROLLER, $event);

    $controller = $event->getController();
    $arguments = $this->resolver->getArguments($request, $controller);

    $response = call_user_func_array($controller, $arguments);

    if (!$response instanceof Response) {
      $event = new GetResponseForControllerResultEvent($this, $request, $type, $response);
      $this->dispatcher->dispatch(KernelEvents::VIEW, $event);

      if ($event->hasResponse()) {
	$response = $event->getResponse();
      }

      if (!$response instanceof Response) {
	$msg = sprintf('The controller must return a response (%s given).', $this->varToString($response));

	if (null === $response) {
	  $msg .= ' Did you forget to add a return statement somewhere in your controller?';
	}
	throw new \LogicException($msg);
      }
    }

    return $this->filterResponse($response, $request, $type);
  }

  private function filterResponse(Response $response, Request $request, $type)
  {
    $event = new FilterResponseEvent($this, $request, $type, $response);
    $this->dispatcher->dispatch(KernelEvents::RESPONSE, $event);

    $this->finishRequest($request, $type);

    return $event->getResponse();
  }

  private function finishRequest(Request $request, $type)
  {
    $this->dispatcher->dispatch(KernelEvents::FINISH_REQUEST, new FinishRequestEvent($this, $request, $type));
    $this->requestStack->pop();
  }

  private function handleException(\Exception $e, $request, $type)
  {
    $event = new GetResponseForExceptionEvent($this, $request, $type, $e);
    $this->dispatcher->dispatch(KernelEvents::EXCEPTION, $event);

    // a listener might have replaced the exception
    $e = $event->getException();

    if (!$event->hasResponse()) {
      $this->finishRequest($request, $type);
      throw $e;
    }

    $response = $event->getResponse();

    // the developer asked for a specific status code
    if ($response->headers->has('X-Status-Code')) {
      $response->setStatusCode($response->headers->get('X-Status-Code'));
      $response->headers->remove('X-Status-Code');
    } elseif (!$response->isClientError() && !$response->isServerError() && !$response->isRedirect()) {
      // ensure that we actually have an error response
      if ($e instanceof HttpExceptionInterface) {
	// keep the HTTP status code and headers
	$response->setStatusCode($e->getStatusCode());
	$response->headers->add($e->getHeaders());
      } else {
	$response->setStatusCode(500);
      }
    }

    try {
      return $this->filterResponse($response, $request, $type);
    } catch (\Exception $e) {
      return $response;
    }
  }

  private function varToString($var)
  {
    if (is_object($var)) {
      return sprintf('Object(%s)', get_class($var));
    }

    if (is_array($var)) {
      $a = array();
      foreach ($var as $k => $v) {
	$a[] = sprintf('%s => %s', $k, $this->varToString($v));
      }

      return sprintf('Array(%s)', implode(', ', $a));
    }

    if (is_resource($var)) {
      return sprintf('Resource(%s)', get_resource_type($var));
    }

    if (null === $var) {
      return 'null';
    }

    if (false === $var) {
      return 'false';
    }

    if (true === $var) {
      return 'true';
    }

    return (string) $var;
  }
}
