<?php namespace Minima\Response;

use Minima\Event\FilterResponseEvent;
use Minima\Kernel\NullHttpKernel;
use Minima\Util\Stringify;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ResponsePreparer implements ResponsePreparerInterface
{
  protected $dispatcher;
 
  public function __construct(EventDispatcherInterface $dispatcher)
  {
    $this->dispatcher = $dispatcher;
  }

  public function validateAndPrepare($response, Request $request)
  {
    $response = $this->manageInvalidResponse($response, $request);
    return $this->prepare($response, $request);
  }
  
  public function prepare(Response $response, Request $request)
  {
    $event = new FilterResponseEvent($request, $response);

    $this->dispatcher->dispatch(KernelEvents::RESPONSE, $event);

    return $event->getResponse();
  }

  private function manageInvalidResponse($response, Request $request)
  {
    if (!$response instanceof Response) {
      $event = new GetResponseForControllerResultEvent(new NullHttpKernel(), $request, HttpKernelInterface::MASTER_REQUEST, $response);
      $this->dispatcher->dispatch(KernelEvents::VIEW, $event);

      if ($event->hasResponse()) {
	$response = $event->getResponse();
      }

      if (!$response instanceof Response) {
	$msg = sprintf('The controller must return a response (%s given).', Stringify::varToString($response));

	// the user may have forgotten to return something
	if (null === $response) {
	  $msg .= ' Did you forget to add a return statement somewhere in your controller?';
	}
	throw new \LogicException($msg);
      }
    }
    return $response;
  }
}
