<?php

use Minima\Listener\LogListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class LogListenerTest extends \PHPUnit_Framework_TestCase {

  public function testOnKernelRequest()
  {
    $logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();
    $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')->disableOriginalConstructor()->getMock();
    $listener = new LogListener($logger);
    $request = Request::create('/route', 'GET');

    $event->expects($this->once())->method('getRequestType')->willReturn(HttpKernelInterface::MASTER_REQUEST);
    $event->expects($this->once())->method('getRequest')->willReturn($request);
    $logger->expects($this->once())->method('info')->with('> GET /route');

    $listener->onKernelRequest($event);
  }

  public function testOnKernelRequestForNonMasterRequest()
  {
    $logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();
    $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')->disableOriginalConstructor()->getMock();
    $listener = new LogListener($logger);
    $request = Request::create('/route', 'GET');

    $event->expects($this->once())->method('getRequestType')->willReturn(HttpKernelInterface::SUB_REQUEST);
    $event->expects($this->never())->method('getRequest')->willReturn($request);
    $logger->expects($this->never())->method('info')->with('> GET /route');

    $listener->onKernelRequest($event);
  }

  public function testOnKernelResponse()
  {
    $logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();
    $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterResponseEvent')->disableOriginalConstructor()->getMock();
    $listener = new LogListener($logger);
    $response = new Response();
    $response->setStatusCode(404);

    $event->expects($this->once())->method('getRequestType')->willReturn(HttpKernelInterface::MASTER_REQUEST);
    $event->expects($this->once())->method('getResponse')->willReturn($response);
    $logger->expects($this->once())->method('info')->with('< 404');

    $listener->onKernelResponse($event);
  }

  public function testOnKernelResponseForNonMasterRequest()
  {
    $logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();
    $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterResponseEvent')->disableOriginalConstructor()->getMock();
    $listener = new LogListener($logger);
    $response = new Response();
    $response->setStatusCode(404);

    $event->expects($this->once())->method('getRequestType')->willReturn(HttpKernelInterface::SUB_REQUEST);
    $event->expects($this->never())->method('getResponse');
    $logger->expects($this->never())->method('info');

    $listener->onKernelResponse($event);
  }

  public function testOnKernelResponseForRedirectResponse()
  {
    $logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();
    $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterResponseEvent')->disableOriginalConstructor()->getMock();
    $listener = new LogListener($logger);
    $response = new RedirectResponse('www.target.url', 302);

    $event->expects($this->once())->method('getRequestType')->willReturn(HttpKernelInterface::MASTER_REQUEST);
    $event->expects($this->once())->method('getResponse')->willReturn($response);
    $logger->expects($this->once())->method('info')->with('< 302 www.target.url');

    $listener->onKernelResponse($event);
  }

  public function testOnKernelException()
  {
    $logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();
    $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent')->disableOriginalConstructor()->getMock();
    $listener = new LogListener($logger);
    $exception = new \Exception();

    $event->expects($this->once())->method('getException')->willReturn($exception);
    $logger->expects($this->once())->method('critical')->with($this->anything(), array('exception' =>$exception));

    $listener->onKernelException($event);
  }

  public function testOnKernelExceptionForHttpException()
  {
    $logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();
    $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent')->disableOriginalConstructor()->getMock();
    $listener = new LogListener($logger);
    $exception = new HttpException(404);

    $event->expects($this->once())->method('getException')->willReturn($exception);
    $logger->expects($this->once())->method('error')->with($this->anything(), array('exception' =>$exception));

    $listener->onKernelException($event);
  }

  public function testOnKernelExceptionForHttpExceptionGreaterThan500()
  {
    $logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();
    $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent')->disableOriginalConstructor()->getMock();
    $listener = new LogListener($logger);
    $exception = new HttpException(500);

    $event->expects($this->once())->method('getException')->willReturn($exception);
    $logger->expects($this->once())->method('critical')->with($this->anything(), array('exception' =>$exception));

    $listener->onKernelException($event);
  }
}
