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
}
