<?php

require_once __DIR__.'/bootstrap.php';

use Minima\Builder\DatabaseBuilder;
use Minima\Builder\LoggerBuilder;
use Minima\Http\Request;
use Minima\Routing\Router;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;

// Configuration
$configuration = array(
    'root' => __DIR__,
);

// Handle the request
$request = Request::createFromGlobals();

// Stateful Components
$dispatcher = new EventDispatcher();
$database = DatabaseBuilder::getConnection();

// Loading routes
$routeCollection = new RouteCollection();

// Add your routes here

// Routing
$logger = LoggerBuilder::build($configuration);
$requestContext = new RequestContext();
$requestContext->fromRequest($request);
$matcher = new UrlMatcher($routeCollection, $requestContext);
$router = new Router($matcher, $logger);

// Build Application
$application = ApplicationFactory::build($configuration, $dispatcher, $router);

$response = $application->handle($request);
$response->send();
