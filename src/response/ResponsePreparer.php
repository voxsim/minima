<?php namespace Minima\Response;

use Minima\Kernel\NullHttpKernel;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ResponsePreparer implements ResponsePreparerInterface
{
  protected $dispatcher;
 
  public function __construct(EventDispatcherInterface $dispatcher)
  {
    $this->dispatcher = $dispatcher;
  }

  public function validateAndPrepare($response, Request $request, $type)
  {
    if (!$response instanceof Response) {
	$event = new GetResponseForControllerResultEvent(new NullHttpKernel(), $request, $type, $response);
	$this->dispatcher->dispatch(KernelEvents::VIEW, $event);

	if ($event->hasResponse()) {
	    $response = $event->getResponse();
	}

	if (!$response instanceof Response) {
	    $msg = sprintf('The controller must return a response (%s given).', $this->varToString($response));

	    // the user may have forgotten to return something
	    if (null === $response) {
		$msg .= ' Did you forget to add a return statement somewhere in your controller?';
	    }
	    throw new \LogicException($msg);
	}
    }

    return $this->prepare($response, $request, $type);
  }
  
  public function prepare(Response $response, Request $request, $type)
  {
      $event = new FilterResponseEvent(new NullHttpKernel(), $request, $type, $response);

      $this->dispatcher->dispatch(KernelEvents::RESPONSE, $event);

      return $event->getResponse();
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
