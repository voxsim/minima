<?php

require_once __DIR__.'/bootstrap.php';

use Minima\Provider\DatabaseProvider;
use Minima\Provider\LoggerProvider;
use Minima\Http\Request;
use Minima\FrontendController\FrontendController;
use Symfony\Component\EventDispatcher\EventDispatcher;

// Configuration
$configuration = array(
    'root' => __DIR__,
);

// Stateful Components
$dispatcher = new EventDispatcher();
$database = DatabaseProvider::getConnection();
$frontendController = FrontendController::build($configuration);

// Add your routes here
// Build Application
$application = ApplicationFactory::build($configuration, $dispatcher, $frontendController);

// Handle the request
$request = Request::createFromGlobals();

$response = $application->handle($request);
$response->send();
