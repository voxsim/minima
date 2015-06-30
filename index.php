<?php

require_once __DIR__.'/bootstrap.php';

use Minima\Builder\DatabaseBuilder;
use Minima\Http\Request;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Routing\RouteCollection;

// Configuration
$configuration = array(
    'root' => __DIR__,
);

// Stateful Components
$dispatcher = new EventDispatcher();

$database = DatabaseBuilder::getConnection();

// Loading routes
$routeCollection = new RouteCollection();

// Add your routes here

// Build Application
$application = ApplicationFactory::build($dispatcher, $routeCollection, $configuration);

// Handle the request
$request = Request::createFromGlobals();

$response = $application->handle($request);
$response->send();
