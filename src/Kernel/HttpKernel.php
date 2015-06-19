<?php
namespace Minima\Kernel;

use Minima\Controller\ControllerResolverInterface;
use Minima\Routing\RouterInterface;
use Minima\Routing\NullRouter;
use Minima\Response\ResponsePreparerInterface;
use Minima\Response\ResponsePreparer;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
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

    public function __construct(EventDispatcherInterface $dispatcher, ControllerResolverInterface $resolver, RequestStack $requestStack = null, RouterInterface $router = null, ResponsePreparerInterface $responsePreparer = null)
    {
        $this->dispatcher = $dispatcher;
        $this->resolver = $resolver;
        $this->requestStack = $requestStack == null ? new RequestStack() : $requestStack;
	$this->router = $router == null ? new NullRouter() : $router;
	$this->responsePreparer = $responsePreparer == null ? new ResponsePreparer($dispatcher) : $responsePreparer;
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
      try {
	$this->requestStack->push($request);

	$event = new GetResponseEvent(new NullHttpKernel(), $request, HttpKernelInterface::MASTER_REQUEST);
	$this->dispatcher->dispatch(KernelEvents::REQUEST, $event);

	if ($event->hasResponse()) {
	  return $this->prepareResponse($event->getResponse(), $request, HttpKernelInterface::MASTER_REQUEST);
	}

	$this->router->lookup($request);

	list($controller, $arguments) = $this->resolver->resolve($request);

	$response = call_user_func_array($controller, $arguments);

	$response = $this->responsePreparer->validateAndPrepare($response, $request);
	$this->finishRequest($request, HttpKernelInterface::MASTER_REQUEST);
	return $response;
      } catch (\Exception $e) {
	if (false === $catch) {
	  $this->finishRequest($request, HttpKernelInterface::MASTER_REQUEST);

	  throw $e;
	}

	return $this->handleException($e, $request);
      }
    }

    public function terminate(Request $request, Response $response)
    {
        $this->dispatcher->dispatch(KernelEvents::TERMINATE, new PostResponseEvent(new NullHttpKernel(), $request, $response));
    }

    private function prepareResponse(Response $response, Request $request)
    {
      $response = $this->responsePreparer->prepare($response, $request);
      $this->finishRequest($request);
      return $response;
    }

    private function finishRequest(Request $request)
    {
        $this->dispatcher->dispatch(KernelEvents::FINISH_REQUEST, new FinishRequestEvent(new NullHttpKernel(), $request, HttpKernelInterface::MASTER_REQUEST));
        $this->requestStack->pop();
    }

    // TODO: How I should refactor this awful piece of code?
    private function handleException(\Exception $e, $request)
    {
        $event = new GetResponseForExceptionEvent(new NullHttpKernel(), $request, HttpKernelInterface::MASTER_REQUEST, $e);
        $this->dispatcher->dispatch(KernelEvents::EXCEPTION, $event);

        // a listener might have replaced the exception
        $e = $event->getException();

        if (!$event->hasResponse()) {
            $this->finishRequest($request, HttpKernelInterface::MASTER_REQUEST);

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

        return $this->prepareResponse($response, $request);
    }
}
