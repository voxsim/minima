<?php

namespace Minima\Listener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LogListener implements EventSubscriberInterface
{
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $this->logRequest($event->getRequest());
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $this->logResponse($event->getResponse());
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $this->logException($event->getException());
    }

    protected function logRequest(Request $request)
    {
        $this->logger->info('> '.$request->getMethod().' '.$request->getRequestUri());
    }

    protected function logResponse(Response $response)
    {
        if ($response instanceof RedirectResponse) {
            $this->logger->info('< '.$response->getStatusCode().' '.$response->getTargetUrl());
        } else {
            $this->logger->info('< '.$response->getStatusCode());
        }
    }

    protected function logException(\Exception $e)
    {
        $message = sprintf('%s: %s (uncaught exception) at %s line %s', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine());

        if ($e instanceof HttpExceptionInterface && $e->getStatusCode() < 500) {
            $this->logger->error($message, array('exception' => $e));
        } else {
            $this->logger->critical($message, array('exception' => $e));
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onKernelRequest', 0),
            KernelEvents::RESPONSE => array('onKernelResponse', 0),
            /*
             * Priority -4 is used to come after those from SecurityServiceProvider (0)
             * but before the error handlers added with Silex\Application::error (defaults to -8)
             */
            KernelEvents::EXCEPTION => array('onKernelException', -4)
        );
    }
}
