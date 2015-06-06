<?php

require_once __DIR__.'/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();

$application = Minima\ApplicationFactory::build();

$response = $application->handle($request);
$response->send();
