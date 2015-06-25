<?php

use Minima\Builder\TwigBuilder;
use Minima\Event\EventDispatcher;
use Minima\Security\NativeSessionTokenStorage;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DebugApplicationIntegrationTest extends ApplicationIntegrationTest {

  protected function getDebugFlag()
  {
    return true;
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
  
  public function testLogging()
  {
    $request = Request::create('/log_hello/Simon');

    $this->application->handle($request);
    $messages = $this->logger->getMessages();

    $this->assertEquals('Message from controller', $messages[0][1]);
  }
  
  public function testUnsecuredPath()
  {
    $request = Request::create('/unsecured_hello');

    $response = $this->application->handle($request);

    $this->assertEquals('Hello anonymous access', $response->getContent());
  }

  public function testSecuredPath()
  {
    $request = Request::create('/secured_hello');
    $request->headers->set('PHP_AUTH_USER', 'Simon');
    $request->headers->set('PHP_AUTH_PW', 'foo');

    $response = $this->application->handle($request);

    $this->assertEquals('Hello Simon', $response->getContent());
  }

  public function testBlockUnknownUserForSecuredPath()
  {
    var_dump($_SESSION);

    $request = Request::create('/secured_hello');

    $response = $this->application->handle($request);

    $this->assertEquals('401', $response->getStatusCode());
  }
}
