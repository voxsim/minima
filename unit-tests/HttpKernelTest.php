<?php

use Minima\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;

class HttpKernelTest extends \PHPUnit_Framework_TestCase {

  public function __construct() {
  }

  public function testHandle() {
    $dispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();
    $resolver = $this->getMockBuilder('Symfony\Component\HttpKernel\Controller\ControllerResolverInterface')->getMock();
    $requestStack = $this->getMockBuilder('Symfony\Component\HttpFoundation\RequestStack')->getMock();
    $router = $this->getMockBuilder('Minima\Routing\RouterInterface')->getMock();
    $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')->getMock();

    $controller = function() { return new Response(''); };
    $arguments = array();

    $dispatcher->expects($this->exactly(4))->method('dispatch')->withConsecutive(
	array(KernelEvents::REQUEST, $this->anything()),
	array(KernelEvents::CONTROLLER, $this->anything()),
	array(KernelEvents::RESPONSE, $this->anything()),
	array(KernelEvents::FINISH_REQUEST, $this->anything())
    );

    $resolver->expects($this->once())->method('getController')->willReturn($controller);
    $resolver->expects($this->once())->method('getArguments')->willReturn($arguments);

    $httpKernel = new HttpKernel($dispatcher, $resolver, $requestStack, $router);
    $response = $httpKernel->handle($request);
  }
}
