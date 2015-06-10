<?php

use Minima\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HttpKernelTest extends \PHPUnit_Framework_TestCase {

  public function __construct() {
    $this->dispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();
    $this->resolver = $this->getMockBuilder('Symfony\Component\HttpKernel\Controller\ControllerResolverInterface')->getMock();
    $this->requestStack = $this->getMockBuilder('Symfony\Component\HttpFoundation\RequestStack')->getMock();
    $this->router = $this->getMockBuilder('Minima\Routing\RouterInterface')->getMock();
    $this->request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')->getMock();

    $this->controller = function() { return new Response(''); };
    $this->arguments = array();

    $this->httpKernel = new HttpKernel($this->dispatcher, $this->resolver, $this->requestStack, $this->router);
  }

  public function testHandle() {
    $this->requestStack->expects($this->once())->method('push');

    $this->dispatcher->expects($this->exactly(4))->method('dispatch')->withConsecutive(
	array(KernelEvents::REQUEST, $this->anything()),
	array(KernelEvents::CONTROLLER, $this->anything()),
	array(KernelEvents::RESPONSE, $this->anything()),
	array(KernelEvents::FINISH_REQUEST, $this->anything())
    );

    $this->router->expects($this->once())->method('lookup');

    $this->resolver->expects($this->once())->method('getController')->willReturn($this->controller);
    $this->resolver->expects($this->once())->method('getArguments')->willReturn($this->arguments);
    
    $this->requestStack->expects($this->once())->method('pop');

    $this->httpKernel->handle($this->request);
  }

  public function testHandleAfterFirstRequestEvent() {
    $this->requestStack->expects($this->once())->method('push');

    $this->dispatcher->expects($this->at(0))->method('dispatch')->with(KernelEvents::REQUEST, $this->anything())
	 ->willReturnCallback(function($type, $event) {
		$event->setResponse(new Response(''));
	 });
    $this->dispatcher->expects($this->at(1))->method('dispatch')->with(KernelEvents::RESPONSE, $this->anything());
    $this->dispatcher->expects($this->at(2))->method('dispatch')->with(KernelEvents::FINISH_REQUEST, $this->anything());

    $this->requestStack->expects($this->once())->method('pop');

    $this->httpKernel->handle($this->request);
  }

  public function testUnableToFindController() {
    try {
      $this->requestStack->expects($this->once())->method('push');

      $this->dispatcher->expects($this->exactly(2))->method('dispatch')->withConsecutive(
	  array(KernelEvents::REQUEST, $this->anything()),
	  array(KernelEvents::FINISH_REQUEST, $this->anything())
      );

      $this->router->expects($this->once())->method('lookup');

      $this->resolver->expects($this->once())->method('getController')->willReturn(false);
      
      $this->httpKernel->handle($this->request, HttpKernelInterface::MASTER_REQUEST, false);
      $this->assertTrue(false);
    } catch(NotFoundHttpException $e) {
      // DO NOTHING
    }
  }

  public function testExceptionIfResponseIsNotAResponse() {
    try {
      $this->requestStack->expects($this->once())->method('push');

      $this->dispatcher->expects($this->exactly(4))->method('dispatch')->withConsecutive(
	  array(KernelEvents::REQUEST, $this->anything()),
	  array(KernelEvents::CONTROLLER, $this->anything()),
	  array(KernelEvents::VIEW, $this->anything()),
	  array(KernelEvents::FINISH_REQUEST, $this->anything())
      );

      $this->router->expects($this->once())->method('lookup');

      $this->resolver->expects($this->once())->method('getController')->willReturn(function() { return ""; });
      $this->resolver->expects($this->once())->method('getArguments')->willReturn($this->arguments);
      
      $this->requestStack->expects($this->once())->method('pop');

      $this->httpKernel->handle($this->request, HttpKernelInterface::MASTER_REQUEST, false);
      $this->assertTrue(false);
    } catch(LogicException $e) {
      // DO NOTHING
    }
  }

  public function testExceptionIfResponseIsNull() {
    try {
      $this->requestStack->expects($this->once())->method('push');

      $this->dispatcher->expects($this->exactly(4))->method('dispatch')->withConsecutive(
	  array(KernelEvents::REQUEST, $this->anything()),
	  array(KernelEvents::CONTROLLER, $this->anything()),
	  array(KernelEvents::VIEW, $this->anything()),
	  array(KernelEvents::FINISH_REQUEST, $this->anything())
      );

      $this->router->expects($this->once())->method('lookup');

      $this->resolver->expects($this->once())->method('getController')->willReturn(function() { });
      $this->resolver->expects($this->once())->method('getArguments')->willReturn($this->arguments);
      
      $this->requestStack->expects($this->once())->method('pop');

      $this->httpKernel->handle($this->request, HttpKernelInterface::MASTER_REQUEST, false);
      $this->assertTrue(false);
    } catch(LogicException $e) {
      // DO NOTHING
    }
  }

  public function testHandleException() {
    try {
      $this->requestStack->expects($this->once())->method('push');

      $this->dispatcher->expects($this->exactly(3))->method('dispatch')->withConsecutive(
	  array(KernelEvents::REQUEST, $this->anything()),
	  array(KernelEvents::EXCEPTION, $this->anything()),
	  array(KernelEvents::FINISH_REQUEST, $this->anything())
      );

      $this->router->expects($this->once())->method('lookup')->willThrowException(new Exception);

      $this->requestStack->expects($this->once())->method('pop');

      $this->httpKernel->handle($this->request);

      $this->assertTrue(false);
    } catch(Exception $e) {
      // DO NOTHING
    }
  }

  public function testHandleExceptionAndNotCatched() {
    try {
      $this->requestStack->expects($this->once())->method('push');

      $this->dispatcher->expects($this->exactly(2))->method('dispatch')->withConsecutive(
	  array(KernelEvents::REQUEST, $this->anything()),
	  array(KernelEvents::FINISH_REQUEST, $this->anything())
      );

      $this->router->expects($this->once())->method('lookup')->willThrowException(new Exception);

      $this->requestStack->expects($this->once())->method('pop');

      $this->httpKernel->handle($this->request, HttpKernelInterface::MASTER_REQUEST, false);
    } catch(Exception $e) {
      // DO NOTHING
    }
  }
}
