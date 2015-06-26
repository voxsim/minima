<?php

use Minima\Builder\TwigBuilder;
use Minima\Event\EventDispatcher;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProductionApplicationIntegrationTest extends ApplicationIntegrationTest {

  protected function getDebugFlag()
  {
    return false;
  }

  public function testNotFoundHandling()
  {
    $response = $this->application->handle(new Request());

    $this->assertEquals('Something went wrong! (No route found for "GET /")', $response->getContent());
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
  
  public function testLogging()
  {
    $request = Request::create('/log_hello/Simon');

    $this->application->handle($request);
    $messages = $this->logger->getMessages();

    $this->assertEquals('Message from controller', $messages[0][1]);
  }
}
