<?php namespace Cache;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;

class SetTtlListener implements EventSubscriberInterface
{
    private $ttl;

    public function __construct($ttl)
    {
	$this->ttl = $ttl;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
	$response = $event->getResponse();
	$response->setTtl($this->ttl);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => 'onKernelResponse',
        );
    }
}
