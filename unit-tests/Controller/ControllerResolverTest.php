<?php

use Minima\Controller\ControllerResolver;
use Minima\Controller\RequestControllerResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestControllerResolverTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->dispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();
        $this->request = Request::create('myrequest');
        $this->controllerResolver = new ControllerResolver();
        $this->requestControllerResolver = new RequestControllerResolver($this->dispatcher, $this->controllerResolver);
    }

  /**
   * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   */
  public function testNotFoundController()
  {
      $this->requestControllerResolver->resolve($this->request);
  }

  /**
   * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   */
  public function testEmptyArrayController()
  {
      $this->request->attributes->set('_controller', array());

      $this->requestControllerResolver->resolve($this->request);
  }

    public function testArrayController()
    {
        $controller = array('controller', 'method');

        $controllerEvent = function ($_, $event) use ($controller) {
            $event->setController($controller);
        };

        $this->dispatcher->expects($this->once())->method('dispatch')->with(KernelEvents::CONTROLLER, $this->anything())->willReturnCallBack($controller);

        $this->request->attributes->set('_controller', $controller);

        $this->requestControllerResolver->resolve($this->request);
    }

  /**
   * @expectedException InvalidArgumentException
   */
  public function testObjectControllerNotCallable()
  {
      $controller = new StdClass();

      $this->request->attributes->set('_controller', $controller);

      $this->requestControllerResolver->resolve($this->request);
  }

    public function testObjectControllerCallable()
    {
        $controller = new controller();

        $controllerEvent = function ($_, $event) use ($controller) {
            $event->setController($controller);
        };

        $this->dispatcher->expects($this->once())->method('dispatch')->with(KernelEvents::CONTROLLER, $this->anything())->willReturnCallBack($controller);

        $this->request->attributes->set('_controller', $controller);

        $this->requestControllerResolver->resolve($this->request);
    }

    public function testStringFunctionCallable()
    {
        $controller = 'method';

        $controllerEvent = function ($_, $event) use ($controller) {
            $event->setController($controller);
        };

        $this->dispatcher->expects($this->once())->method('dispatch')->with(KernelEvents::CONTROLLER, $this->anything())->willReturnCallBack($controller);

        $this->request->attributes->set('_controller', $controller);

        $this->requestControllerResolver->resolve($this->request);
    }

    public function testStringControllerCallable()
    {
        $controller = 'controller';

        $controllerEvent = function ($_, $event) use ($controller) {
        };

        $this->dispatcher->expects($this->once())->method('dispatch')->with(KernelEvents::CONTROLLER, $this->anything())->willReturnCallBack($controllerEvent);

        $this->request->attributes->set('_controller', $controller);

        $this->requestControllerResolver->resolve($this->request);
    }

    public function testStringControllerAndFunctionCallable()
    {
        $controller = 'controller::method';

        $controllerEvent = function ($_, $event) use ($controller) {
            $event->setController($controller);
        };

        $this->dispatcher->expects($this->once())->method('dispatch')->with(KernelEvents::CONTROLLER, $this->anything())->willReturnCallBack($controller);

        $this->request->attributes->set('_controller', $controller);

        $this->requestControllerResolver->resolve($this->request);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testStringNotExistingControllerAndFunctionCallable()
    {
        $controller = 'controller_not_existed::method';

        $controllerEvent = function ($_, $event) use ($controller) {
            $event->setController($controller);
        };

        $this->request->attributes->set('_controller', $controller);

        $this->requestControllerResolver->resolve($this->request);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testStringControllerAndFunctionUncallable()
    {
        $controller = 'controller::uncallable_method';

        $controllerEvent = function ($_, $event) use ($controller) {
            $event->setController($controller);
        };

        $this->request->attributes->set('_controller', $controller);

        $this->requestControllerResolver->resolve($this->request);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testStringUncallableController()
    {
        $controller = 'uncallable_controller';

        $controllerEvent = function ($_, $event) use ($controller) {
            $event->setController($controller);
        };

        $this->request->attributes->set('_controller', $controller);

        $this->requestControllerResolver->resolve($this->request);
    }

    public function testMethodWithOneDefaultParameter()
    {
        $controller = 'controller::methodTwo';

        $controllerEvent = function ($_, $event) use ($controller) {
            $event->setController($controller);
        };

        $this->dispatcher->expects($this->once())->method('dispatch')->with(KernelEvents::CONTROLLER, $this->anything())->willReturnCallBack($controller);

        $this->request->attributes->set('_controller', $controller);

        $this->requestControllerResolver->resolve($this->request);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testMethodWithOneParameter()
    {
        $controller = 'controller::methodThree';

        $controllerEvent = function ($_, $event) use ($controller) {
            $event->setController($controller);
        };

        $this->dispatcher->expects($this->once())->method('dispatch')->with(KernelEvents::CONTROLLER, $this->anything())->willReturnCallBack($controller);

        $this->request->attributes->set('_controller', $controller);

        $this->requestControllerResolver->resolve($this->request);
    }
}

class controller
{
    private static function uncallable_method()
    {
    }

    public static function method()
    {
    }

    public static function methodTwo($parameter = 'parameter')
    {
    }

    public static function methodThree($parameter)
    {
    }

    public function __invoke()
    {
    }
}

class uncallable_controller
{
}

function method()
{
}
