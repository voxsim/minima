<?php

use Minima\Listener\LogListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class LogListenerTest extends \PHPUnit_Framework_TestCase {

  public function setUp()
  {
    $this->logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();
    $this->listener = new LogListener($this->logger);

    $this->requestEvent = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')->disableOriginalConstructor()->getMock();
    $this->responseEvent = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterResponseEvent')->disableOriginalConstructor()->getMock();
    $this->exceptionEvent = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent')->disableOriginalConstructor()->getMock();
  }

  public function testOnKernelRequest()
  {
    $request = Request::create('/route', 'GET');

    $this->requestEvent->expects($this->once())->method('getRequest')->willReturn($request);
    $this->logger->expects($this->once())->method('info')->with('> GET /route');

    $this->listener->onKernelRequest($this->requestEvent);
  }

  public function testOnKernelResponse()
  {
    $response = new Response();
    $response->setStatusCode(404);

    $this->responseEvent->expects($this->once())->method('getResponse')->willReturn($response);
    $this->logger->expects($this->once())->method('info')->with('< 404');

    $this->listener->onKernelResponse($this->responseEvent);
  }

  public function testOnKernelResponseForRedirectResponse()
  {
    $response = new RedirectResponse('www.target.url', 302);

    $this->responseEvent->expects($this->once())->method('getResponse')->willReturn($response);
    $this->logger->expects($this->once())->method('info')->with('< 302 www.target.url');

    $this->listener->onKernelResponse($this->responseEvent);
  }

  public function testOnKernelException()
  {
    $exception = new \Exception();

    $this->exceptionEvent->expects($this->once())->method('getException')->willReturn($exception);
    $this->logger->expects($this->once())->method('critical')->with($this->anything(), array('exception' =>$exception));

    $this->listener->onKernelException($this->exceptionEvent);
  }

  public function testOnKernelExceptionForHttpException()
  {
    $exception = new HttpException(404);

    $this->exceptionEvent->expects($this->once())->method('getException')->willReturn($exception);
    $this->logger->expects($this->once())->method('error')->with($this->anything(), array('exception' =>$exception));

    $this->listener->onKernelException($this->exceptionEvent);
  }

  public function testOnKernelExceptionForHttpExceptionGreaterThan500()
  {
    $exception = new HttpException(500);

    $this->exceptionEvent->expects($this->once())->method('getException')->willReturn($exception);
    $this->logger->expects($this->once())->method('critical')->with($this->anything(), array('exception' =>$exception));

    $this->listener->onKernelException($this->exceptionEvent);
  }
}
