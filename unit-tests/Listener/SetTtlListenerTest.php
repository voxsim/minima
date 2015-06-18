<?php

use Minima\Listener\SetTtlListener;
use Symfony\Component\HttpFoundation\Response;

class SetTtlListenerTest extends \PHPUnit_Framework_TestCase {

  public function testOnKernelResponse()
  {
    $ttl = 10;
    $response = new Response();
    $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterResponseEvent')->disableOriginalConstructor()->getMock();
    $listener = new SetTtlListener($ttl);

    $event->expects($this->once())->method('getResponse')->willReturn($response);
    $this->assertEquals(NULL, $response->getTtl());

    $listener->onKernelResponse($event);    

    $this->assertEquals($ttl, $response->getTtl()); 
  }
}
