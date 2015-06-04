<?php

require_once __DIR__.'/vendor/autoload.php';
 
use Symfony\Component\HttpFoundation\Request;

$configuration = array(
  'charset' => 'UTF-8',
  'debug' => true,
  'twig.path' => __DIR__.'/views',
  'cache.path' =>  __DIR__.'/cache',
  'cache.page' => 10
);

$request = Request::createFromGlobals();

$application = new Application($configuration);

$response = $application->handle($request);
$response->send();
