<?php

require_once __DIR__.'/vendor/autoload.php';

use Minima\ApplicationFactory;
use Minima\Database;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouteCollection;

// Configuration
$configuration = array();

// Steteful Componenents
$dispatcher = new EventDispatcher();

// Database
$database = Database::getConnection();

// Loading routes
$routeCollection = new RouteCollection();

// Add your routes here

// Build Application
$application = ApplicationFactory::build($dispatcher, $routeCollection, $configuration);

// Handle the request
$request = Request::createFromGlobals();

$response = $application->handle($request);
$response->send();
