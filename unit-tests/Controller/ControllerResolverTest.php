<?php

use Minima\Controller\ControllerResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ControllerResolverTest extends \PHPUnit_Framework_TestCase {

  public function setUp()
  {
    $this->dispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();
    $this->request = Request::create('myrequest');
    $this->controllerResolver = new ControllerResolver($this->dispatcher);
  }

  /**
   * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   */
  public function testNotFoundController()
  {
    $this->controllerResolver->resolve($this->request, HttpKernelInterface::MASTER_REQUEST);
  }

  /**
   * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   */
  public function testEmptyArrayController()
  {
    $this->request->attributes->set('_controller', array());

    $this->controllerResolver->resolve($this->request, HttpKernelInterface::MASTER_REQUEST);
  }

  public function testArrayController()
  {
    $controller = array('controller', 'method');

    $controllerEvent = function($_, $event) use($controller) {
      $event->setController($controller);      
    };

    $this->dispatcher->expects($this->once())->method('dispatch')->with(KernelEvents::CONTROLLER, $this->anything())->willReturnCallBack($controller);

    $this->request->attributes->set('_controller', $controller);

    $this->controllerResolver->resolve($this->request, HttpKernelInterface::MASTER_REQUEST);
  }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testObjectControllerNotCallable()
  {
    $controller = new StdClass();

    $this->request->attributes->set('_controller', $controller);

    $this->controllerResolver->resolve($this->request, HttpKernelInterface::MASTER_REQUEST);
  }

  public function testObjectControllerCallable()
  {
    $controller = new controller();

    $controllerEvent = function($_, $event) use($controller) {
      $event->setController($controller);
    };

    $this->dispatcher->expects($this->once())->method('dispatch')->with(KernelEvents::CONTROLLER, $this->anything())->willReturnCallBack($controller);

    $this->request->attributes->set('_controller', $controller);

    $this->controllerResolver->resolve($this->request, HttpKernelInterface::MASTER_REQUEST);
  }

  public function testStringControllerCallable()
  {
    $controller = new controller();

    $controllerEvent = function($_, $event) use($controller) {
      $event->setController($controller);
    };

    $this->dispatcher->expects($this->once())->method('dispatch')->with(KernelEvents::CONTROLLER, $this->anything())->willReturnCallBack($controller);

    $this->request->attributes->set('_controller', $controller);

    $this->controllerResolver->resolve($this->request, HttpKernelInterface::MASTER_REQUEST);
  }
}

class controller {
  public static function method() {
  }
 
  public function __invoke() {
  }
}
