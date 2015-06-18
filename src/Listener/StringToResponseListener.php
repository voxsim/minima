<?php namespace Minima\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;

class StringToResponseListener implements EventSubscriberInterface
{
  public function onKernelView(GetResponseForControllerResultEvent $event)
  {
    $response = $event->getControllerResult();
    if (!$response instanceof Response) {
      $event->setResponse(new Response($response));
    }
  }

  public static function getSubscribedEvents()
  {
    return array(
      KernelEvents::VIEW => 'onKernelView',
    );
  }
}
