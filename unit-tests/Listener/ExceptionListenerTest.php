<?php

use Minima\Listener\ExceptionListener;

class ExceptionListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testOnKernelException()
    {
        $listener = new ExceptionListener();

        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent')->disableOriginalConstructor()->getMock();
        $exception = new \Exception('message');

        $event->expects($this->once())->method('getException')->willReturn($exception);
        $event->expects($this->once())->method('setResponse')->with(new ContentIsEqualTo('Something went wrong! (message)'));

        $listener->onKernelException($event);
    }
}
