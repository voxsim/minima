<?php

require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationTest extends \PHPUnit_Framework_TestCase {

  /**
   *  @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   */
  public function testNotFoundHandling()
  {
      $dispatcher = new EventDispatcher();
      $routes = new RouteCollection();
      $application = new Application($routes, $dispatcher, false);

      $response = $application->handle(new Request());

      $this->assertEquals(404, $response->getStatusCode());
  }
  
  public function testRoute()
  {
      $request = Request::create('/hello/Simon');

      $dispatcher = new EventDispatcher();
      $routes = new RouteCollection();
      $routes->add('hello', new Route('/hello/{name}', array(
	'name' => 'World',
	'_controller' => function($name) { 
	  return 'Hello ' . $name;
	}
      )));
      $application = new Application($routes, $dispatcher, false);

      $response = $application->handle($request);

      $this->assertEquals('Hello Simon', $response->getContent());
  }

  public function testRouteAndTwig()
  {
      $request = Request::create('/hello/Simon');

      $twig = new \Twig(array('twig.path' => __DIR__.'/views'));
      $dispatcher = new EventDispatcher();
      $routes = new RouteCollection();
      $routes->add('hello', new Route('/hello/{name}', array(
	'name' => 'World',
	'_controller' => function ($name) use ($twig) {
	  return $twig->render('hello.twig', array('name' => $name));
	}
      )));
      $application = new Application($routes, $dispatcher, false);

      $response = $application->handle($request);

      $this->assertEquals('Hello Simon' . "\n", $response->getContent());
  }
}
