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
        $exception = $event->getException();
        $flattenException = FlattenException::create($exception);

        if ($exception instanceof \Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException) {
            $msg = 'Access Denied!';
        } else {
            $msg = 'Something went wrong! ('.$flattenException->getMessage().')';
        }

        $event->setResponse(new Response($msg, $flattenException->getStatusCode()));
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => array('onKernelException', -128)
        );
    }
}
