<?php

namespace Minima\Listener;

use Minima\Http\ResponseMaker;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;

class StringToResponseListener implements EventSubscriberInterface
{
    private $responseMaker;

    public function __construct(ResponseMaker $responseMaker)
    {
        $this->responseMaker = $responseMaker;
    }

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $response = $event->getControllerResult();
        if (!$response instanceof Response) {
            $event->setResponse($this->responseMaker->create($response));
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::VIEW => 'onKernelView'
        );
    }
}
