<?php

use Minima\Listener\StringToResponseListener;
use Symfony\Component\HttpFoundation\Response;

class StringToResponseListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testOnKernelViewWhenResponseIsNotValid()
    {
        $responseMaker = $this->getMockBuilder('Minima\Http\ResponseMaker')->disableOriginalConstructor()->getMock();
        $listener = new StringToResponseListener($responseMaker);

        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent')->disableOriginalConstructor()->getMock();

        $response = new Response('string-response');

        $responseMaker->expects($this->once())->method('create')->willReturn($response);

        $event->expects($this->once())->method('getControllerResult')->willReturn('string-response');
        $event->expects($this->once())->method('setResponse')->with($response);

        $listener->onKernelView($event);
    }

    public function testOnKernelView()
    {
        $responseMaker = $this->getMockBuilder('Minima\Http\ResponseMaker')->disableOriginalConstructor()->getMock();
        $listener = new StringToResponseListener($responseMaker);

        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent')->disableOriginalConstructor()->getMock();

        $responseMaker->expects($this->never())->method('create');

        $event->expects($this->once())->method('getControllerResult')->willReturn(new Response());
        $event->expects($this->never())->method('setResponse');

        $listener->onKernelView($event);
    }
}
