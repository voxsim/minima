<?php

require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationDebugIntegrationTest extends \PHPUnit_Framework_TestCase {
  private $application;

  public function __construct()
  {
    $this->application = $this->createApplication();
  }

  public function testNotFoundHandling()
  {
    try {
      $response = $this->application->handle(new Request());
      throw new \RuntimeException("Application, in debug mode, should throw NotFoundHttpException");
    } catch(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
      $this->assertTrue(true);
    }
  }
  
  public function testRoute()
  {
    $request = Request::create('/hello/Simon');

    $response = $this->application->handle($request);

    $this->assertEquals('Hello Simon', $response->getContent());
  }

  public function testTwig()
  {
    $request = Request::create('/twig_hello/Simon');

    $response = $this->application->handle($request);

    $this->assertEquals('Hello Simon' . "\n", $response->getContent());
  }
  
  public function testCaching()
  {
    $request = Request::create('/rand_hello/Simon');

    $response1 = $this->application->handle($request);
    $response2 = $this->application->handle($request);

    $this->assertNotEquals($response1->getContent(), $response2->getContent());
  }

  private function createApplication()
  {
    $testConfiguration = array(
			  'twig.path' => __DIR__.'/views',
			  'cache.path' =>  __DIR__.'/cache',
			);
    return \Minima\ApplicationFactory::buildForDebug($testConfiguration);
  }
}