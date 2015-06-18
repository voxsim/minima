<?php

use Minima\Kernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Exception\HttpException; 

class HttpKernelTest extends \PHPUnit_Framework_TestCase {

  public function __construct()
  {
    $this->dispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();
    $this->resolver = $this->getMockBuilder('Minima\Controller\ControllerResolverInterface')->getMock();
    $this->requestStack = $this->getMockBuilder('Symfony\Component\HttpFoundation\RequestStack')->getMock();
    $this->router = $this->getMockBuilder('Minima\Routing\RouterInterface')->getMock();
    $this->responsePreparer = $this->getMockBuilder('Minima\Response\ResponsePreparerInterface')->getMock();
    $this->request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')->getMock();

    $this->controller = function() { return new Response(''); };
    $this->arguments = array();

    $this->httpKernel = new HttpKernel($this->dispatcher, $this->resolver, $this->requestStack, $this->router, $this->responsePreparer);
  }

  public function testHandle()
  {
    $this->requestStack->expects($this->once())->method('push');
    $this->dispatcher->expects($this->at(0))->method('dispatch')->with(KernelEvents::REQUEST, $this->anything());
    $this->router->expects($this->once())->method('lookup');
    $this->resolver->expects($this->once())->method('resolve')->willReturn(array($this->controller, $this->arguments));
    $this->responsePreparer->expects($this->once())->method('validateAndPrepare');
    $this->dispatcher->expects($this->at(1))->method('dispatch')->with(KernelEvents::FINISH_REQUEST, $this->anything());
    $this->requestStack->expects($this->once())->method('pop');

    $this->httpKernel->handle($this->request);
  }

  public function testHandleWhenEventRequestReturnResponse()
  {
    $requestCallback = function ($_, $event) {
	$event->setResponse(new Response('hello'));
    };

    $this->requestStack->expects($this->once())->method('push');
    $this->dispatcher->expects($this->at(0))->method('dispatch')->with(KernelEvents::REQUEST, $this->anything())->willReturnCallback($requestCallback);
    $this->router->expects($this->never())->method('lookup');
    $this->resolver->expects($this->never())->method('resolve');
    $this->responsePreparer->expects($this->once())->method('prepare')->willReturn(new Response('hello'));
    $this->dispatcher->expects($this->at(1))->method('dispatch')->with(KernelEvents::FINISH_REQUEST, $this->anything());
    $this->requestStack->expects($this->once())->method('pop');

    $this->assertEquals('hello', $this->httpKernel->handle($this->request)->getContent());
  }

  /**
   * @expectedException \Exception
   */
  public function testHandleWithinExceptionAndCatchIsFalse()
  {
    $this->requestStack->expects($this->once())->method('push');
    $this->dispatcher->expects($this->at(0))->method('dispatch')->with(KernelEvents::REQUEST, $this->anything())->willThrowException(new \Exception());
    $this->dispatcher->expects($this->at(1))->method('dispatch')->with(KernelEvents::FINISH_REQUEST, $this->anything());
    $this->requestStack->expects($this->once())->method('pop');

    $this->httpKernel->handle($this->request, HttpKernelInterface::MASTER_REQUEST, false);
  }

  /**
   * @expectedException \Exception
   */
  public function testHandleWithinExceptionAndCatchIsTrue()
  {
    $this->requestStack->expects($this->once())->method('push');
    $this->dispatcher->expects($this->at(0))->method('dispatch')->with(KernelEvents::REQUEST, $this->anything())->willThrowException(new \Exception());
    $this->dispatcher->expects($this->at(1))->method('dispatch')->with(KernelEvents::EXCEPTION, $this->anything());
    $this->dispatcher->expects($this->at(2))->method('dispatch')->with(KernelEvents::FINISH_REQUEST, $this->anything());
    $this->requestStack->expects($this->once())->method('pop');

    $this->httpKernel->handle($this->request, HttpKernelInterface::MASTER_REQUEST, true);
  }

  public function testHandleWithinExceptionAndCatchIsTrueWithAnExceptionHandlingListener()
  {
    $exceptionCallback = function ($_, $event) {
	$event->setResponse(new Response($event->getException()->getMessage()));
    };

    $responseCallback = function($response) {
	return $response;
    };

    $this->requestStack->expects($this->once())->method('push');
    $this->dispatcher->expects($this->at(0))->method('dispatch')->with(KernelEvents::REQUEST, $this->anything());
    $this->router->expects($this->once())->method('lookup');
    $this->resolver->expects($this->once())->method('resolve')->willThrowException(new \RuntimeException('foo'));
    $this->dispatcher->expects($this->at(1))->method('dispatch')->with(KernelEvents::EXCEPTION, $this->anything())->willReturnCallback($exceptionCallback);
    $this->responsePreparer->expects($this->once())->method('prepare')->willReturnCallback($responseCallback);
    $this->dispatcher->expects($this->at(2))->method('dispatch')->with(KernelEvents::FINISH_REQUEST, $this->anything());
    $this->requestStack->expects($this->once())->method('pop');

    $response = $this->httpKernel->handle($this->request, HttpKernelInterface::MASTER_REQUEST, true);

    $this->assertEquals('foo', $response->getContent());
    $this->assertEquals('500', $response->getStatusCode());
  }

  public function testHandleWithinExceptionAndCatchIsTrueWithAnExceptionHandlingListenerThatSetStatusCode()
  {
    $exceptionCallback = function ($_, $event) {
	$response = new Response($event->getException()->getMessage());
	$response->headers->set('X-Status-Code', 404);
	$event->setResponse($response);
    };

    $responseCallback = function($response) {
	return $response;
    };

    $this->requestStack->expects($this->once())->method('push');
    $this->dispatcher->expects($this->at(0))->method('dispatch')->with(KernelEvents::REQUEST, $this->anything());
    $this->router->expects($this->once())->method('lookup');
    $this->resolver->expects($this->once())->method('resolve')->willThrowException(new \RuntimeException('foo'));
    $this->dispatcher->expects($this->at(1))->method('dispatch')->with(KernelEvents::EXCEPTION, $this->anything())->willReturnCallback($exceptionCallback);
    $this->responsePreparer->expects($this->once())->method('prepare')->willReturnCallback($responseCallback);
    $this->dispatcher->expects($this->at(2))->method('dispatch')->with(KernelEvents::FINISH_REQUEST, $this->anything());
    $this->requestStack->expects($this->once())->method('pop');

    $response = $this->httpKernel->handle($this->request, HttpKernelInterface::MASTER_REQUEST, true);

    $this->assertEquals('foo', $response->getContent());
    $this->assertEquals('404', $response->getStatusCode());
    $this->assertFalse($response->headers->has('X-Status-Code'));
  }

  public function testHandleWithinExceptionAndCatchIsTrueWithAnHttpExceptionInterface()
  {
    $exceptionCallback = function ($_, $event) {
	$response = new Response($event->getException()->getMessage());
	$event->setResponse($response);
	$event->setException(new HttpException(502, null, null, array('header1' => 'value1')));
    };

    $responseCallback = function($response) {
	return $response;
    };

    $this->requestStack->expects($this->once())->method('push');
    $this->router->expects($this->once())->method('lookup');
    $this->resolver->expects($this->once())->method('resolve')->willThrowException(new \RuntimeException('foo'));
    $this->dispatcher->expects($this->at(1))->method('dispatch')->with(KernelEvents::EXCEPTION, $this->anything())->willReturnCallback($exceptionCallback);
    $this->responsePreparer->expects($this->once())->method('prepare')->willReturnCallback($responseCallback);
    $this->dispatcher->expects($this->at(2))->method('dispatch')->with(KernelEvents::FINISH_REQUEST, $this->anything());
    $this->requestStack->expects($this->once())->method('pop');

    $response = $this->httpKernel->handle($this->request, HttpKernelInterface::MASTER_REQUEST, true);

    $this->assertEquals('foo', $response->getContent());
    $this->assertEquals('502', $response->getStatusCode());
    $this->assertEquals('value1', $response->headers->get('header1'));
  }

  public function testTerminate() {
    $this->dispatcher->expects($this->once())->method('dispatch')->with(KernelEvents::TERMINATE, $this->anything());

    $this->httpKernel->terminate($this->request, new Response());
  }
}
