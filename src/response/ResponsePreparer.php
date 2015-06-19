<?php namespace Minima\Response;

use Minima\Event\GetResponseForControllerResultEvent;
use Minima\Event\FilterResponseEvent;
use Minima\Kernel\NullHttpKernel;
use Minima\Util\Stringify;
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

  public function prepare($response, Request $request)
  {
    if (!$response instanceof Response) {
      $event = new GetResponseForControllerResultEvent($request, $response);
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

    $event = new FilterResponseEvent($request, $response);

    $this->dispatcher->dispatch(KernelEvents::RESPONSE, $event);

    return $event->getResponse();
  }
}
