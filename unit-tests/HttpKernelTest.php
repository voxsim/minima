<?php

use Minima\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\EventDispatcher\EventDispatcher;

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

  // TODO: Is it possible to refactor with mock?
  public function testHandleWhenEventRequestReturnResponse()
  {
    $dispatcher = new EventDispatcher();
    $dispatcher->addListener(KernelEvents::REQUEST, function ($event) {
	$event->setResponse(new Response('hello'));
    });

    $this->httpKernel = new HttpKernel($dispatcher, $this->resolver, $this->requestStack, $this->router, $this->responsePreparer);

    $this->requestStack->expects($this->once())->method('push');
    $this->router->expects($this->never())->method('lookup');
    $this->resolver->expects($this->never())->method('resolve');
    $this->responsePreparer->expects($this->once())->method('prepare')->willReturn(new Response('hello'));
    $this->requestStack->expects($this->once())->method('pop');

    $this->assertEquals('hello', $this->httpKernel->handle($this->request)->getContent());
  }

  /**
   * @expectedException \Exception
   */
  public function testHandleWithinExceptionAndNotCatched()
  {
    $this->requestStack->expects($this->once())->method('push');
    $this->dispatcher->expects($this->at(0))->method('dispatch')->with(KernelEvents::REQUEST, $this->anything())->willThrowException(new \Exception());
    $this->dispatcher->expects($this->at(1))->method('dispatch')->with(KernelEvents::FINISH_REQUEST, $this->anything());
    $this->requestStack->expects($this->once())->method('pop');

    $this->httpKernel->handle($this->request, HttpKernelInterface::MASTER_REQUEST, false);
  }
}
