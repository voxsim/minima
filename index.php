<?php

require_once __DIR__.'/bootstrap.php';

use Minima\Provider\DatabaseProvider;
use Minima\Provider\LoggerProvider;
use Minima\Http\Request;
use Minima\FrontendController\FrontendController;
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
$database = DatabaseProvider::getConnection();

// Loading routes
$routeCollection = new RouteCollection();

// Add your routes here

// Routing
$logger = LoggerProvider::build($configuration);
$requestContext = new RequestContext();
$requestContext->fromRequest($request);
$matcher = new UrlMatcher($routeCollection, $requestContext);
$frontendController = new FrontendController($matcher, $logger);

// Build Application
$application = ApplicationFactory::build($configuration, $dispatcher, $frontendController);

$response = $application->handle($request);
$response->send();
