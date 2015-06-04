<?php

require_once __DIR__.'/vendor/autoload.php';
 
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\EventDispatcher;

$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();

$configuration = array(
  'charset' => 'UTF-8',
  'debug' => true,
  'twig.path' => __DIR__.'/views'
);

$twig = new Twig($configuration);
 
$routes = new \Routing\ApplicationRouteCollection($twig);

$application = new Application($routes, $dispatcher, $configuration['debug']);

$request = Request::createFromGlobals();
 
$response = $application->handle($request);
$response->send();
