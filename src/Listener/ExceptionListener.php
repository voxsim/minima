<?php

namespace Minima\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionListener implements EventSubscriberInterface
{
  public function onKernelException(GetResponseForExceptionEvent $event)
  {
    $exception = FlattenException::create($event->getException());
    
    $msg = 'Something went wrong! ('.$exception->getMessage().')';

    $event->setResponse(new Response($msg, $exception->getStatusCode()));
  }

  public static function getSubscribedEvents()
  {
    return array(
      KernelEvents::EXCEPTION => array('onKernelException', -128),
    );
  }
}
