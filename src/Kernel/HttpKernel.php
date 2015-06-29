<?php

namespace Minima\Kernel;

use Minima\Controller\ControllerResolverInterface;
use Minima\Event\FinishRequestEvent;
use Minima\Event\GetResponseEvent;
use Minima\Event\GetResponseForExceptionEvent;
use Minima\Event\PostResponseEvent;
use Minima\Routing\RouterInterface;
use Minima\Routing\NullRouter;
use Minima\Response\ResponsePreparerInterface;
use Minima\Response\ResponsePreparer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class HttpKernel implements HttpKernelInterface, TerminableInterface
{
    protected $dispatcher;
    protected $resolver;
    protected $requestStack;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        ControllerResolverInterface $resolver,
        RequestStack $requestStack = null,
        RouterInterface $router = null,
        ResponsePreparerInterface $responsePreparer = null
    )
    {
        $this->dispatcher = $dispatcher;
        $this->resolver = $resolver;
        $this->requestStack = $requestStack === null ? new RequestStack() : $requestStack;
        $this->router = $router === null ? new NullRouter() : $router;
        $this->responsePreparer = $responsePreparer === null ? new ResponsePreparer($dispatcher) : $responsePreparer;
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        try {
            $this->requestStack->push($request);

            $event = new GetResponseEvent($request);
            $this->dispatcher->dispatch(KernelEvents::REQUEST, $event);

            if ($event->hasResponse()) {
                return $this->prepareResponse($event->getResponse(), $request);
            }

            $this->router->lookup($request);

            list($controller, $arguments) = $this->resolver->resolve($request);

            $response = call_user_func_array($controller, $arguments);

            return $this->prepareResponse($response, $request);
        } catch (\Exception $e) {
            if (false === $catch) {
                $this->finishRequest($request);

                throw $e;
            }

            return $this->handleException($e, $request);
        }
    }

    public function terminate(Request $request, Response $response)
    {
        $this->dispatcher->dispatch(KernelEvents::TERMINATE, new PostResponseEvent($request, $response));
    }

    private function prepareResponse($response, Request $request)
    {
        $response = $this->responsePreparer->prepare($response, $request);
        $this->finishRequest($request);

        return $response;
    }

    private function finishRequest(Request $request)
    {
        $this->dispatcher->dispatch(KernelEvents::FINISH_REQUEST, new FinishRequestEvent($request));
        $this->requestStack->pop();
    }

  // TODO: How I should refactor this awful piece of code?
  private function handleException(\Exception $exception, $request)
  {
      $event = new GetResponseForExceptionEvent($request, $exception);
      $this->dispatcher->dispatch(KernelEvents::EXCEPTION, $event);

      // a listener might have replaced the exception
      $exception = $event->getException();

      if (!$event->hasResponse()) {
          $this->finishRequest($request);

          throw $exception;
      }

      $response = $event->getResponse();

      // the developer asked for a specific status code
      if ($response->headers->has('X-Status-Code')) {
          $response->setStatusCode($response->headers->get('X-Status-Code'));
          $response->headers->remove('X-Status-Code');
      } elseif (!$response->isClientError() && !$response->isServerError() && !$response->isRedirect()) {
          // ensure that we actually have an error response
          if ($exception instanceof HttpExceptionInterface) {
              // keep the HTTP status code and headers
              $response->setStatusCode($exception->getStatusCode());
              $response->headers->add($exception->getHeaders());
          } else {
              $response->setStatusCode(500);
          }
      }

      return $this->prepareResponse($response, $request);
  }
}
