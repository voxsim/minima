<?php

namespace Minima\Listener;

use Minima\Http\ResponseMaker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionListener implements EventSubscriberInterface
{
    private $responseMaker;

    public function __construct(ResponseMaker $responseMaker)
    {
        $this->responseMaker = $responseMaker;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $flattenException = FlattenException::create($exception);
        $msg = 'Something went wrong! ('.$flattenException->getMessage().')';
        $event->setResponse($this->responseMaker->create($msg, $flattenException->getStatusCode()));
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => array('onKernelException', -128)
        );
    }
}
