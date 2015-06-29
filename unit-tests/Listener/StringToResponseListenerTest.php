<?php

use Minima\Listener\StringToResponseListener;
use Symfony\Component\HttpFoundation\Response;

class StringToResponseListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testOnKernelViewWhenResponseIsNotValid()
    {
        $listener = new StringToResponseListener();

        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent')->disableOriginalConstructor()->getMock();

        $event->expects($this->once())->method('getControllerResult')->willReturn('string-response');
        $event->expects($this->once())->method('setResponse');

        $listener->onKernelView($event);
    }

    public function testOnKernelView()
    {
        $listener = new StringToResponseListener();

        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent')->disableOriginalConstructor()->getMock();

        $event->expects($this->once())->method('getControllerResult')->willReturn(new Response());
        $event->expects($this->never())->method('setResponse');

        $listener->onKernelView($event);
    }
}
