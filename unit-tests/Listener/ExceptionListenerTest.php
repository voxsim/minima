<?php

use Minima\Listener\ExceptionListener;
use Symfony\Component\HttpFoundation\Response;

class ExceptionListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testOnKernelException()
    {
        $responseMaker = $this->getMockBuilder('Minima\Http\ResponseMaker')->disableOriginalConstructor()->getMock();
        $listener = new ExceptionListener($responseMaker);

        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent')->disableOriginalConstructor()->getMock();
        $exception = new \Exception('message');

        $response = new Response('Something went wrong! (message)', 500);

        $responseMaker->expects($this->once())->method('create')->willReturn($response);

        $event->expects($this->once())->method('getException')->willReturn($exception);
        $event->expects($this->once())->method('setResponse')->with($response);

        $listener->onKernelException($event);
    }
}
