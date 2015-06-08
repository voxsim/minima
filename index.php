<?php

require_once __DIR__.'/vendor/autoload.php';

use Minima\ApplicationFactory;
use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();

$application = ApplicationFactory::build();

$response = $application->handle($request);
$response->send();
