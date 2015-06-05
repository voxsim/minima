<?php

require_once __DIR__.'/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();

$application = new Minima\Application();

$response = $application->handle($request);
$response->send();
