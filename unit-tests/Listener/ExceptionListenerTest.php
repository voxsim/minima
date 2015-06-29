<?php

use Minima\Listener\ExceptionListener;
use Symfony\Component\HttpFoundation\Response;

class ExceptionListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testOnKernelException()
    {
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent')->disableOriginalConstructor()->getMock();
        $listener = new ExceptionListener();
        $exception = new \Exception('message');

        $event->expects($this->once())->method('getException')->willReturn($exception);
        $event->expects($this->once())->method('setResponse')->with(new Response('Something went wrong! (message)', 500));

        $listener->onKernelException($event);
    }
}
