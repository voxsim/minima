<?php namespace Minima\Routing;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\HttpKernel\EventListener\RouterListener;

class Router extends RouterListener {
  public function __construct($configuration) {
    $routeCollection = new RouteCollection();
    $routeCollection->add('hello', new route('/hello/{name}', array(
      'name' => 'world',
      '_controller' => function($name) { 
	return 'Hello ' . $name;
      }
    )));
    
    $routeCollection->add('twig_hello', new Route('/twig_hello/{name}', array(
      'name' => 'World',
      '_controller' => function ($name) use($configuration) {
	$twig = \Minima\Twig::create($configuration);
	return $twig->render('hello.twig', array('name' => $name));
      }
    )));

    $routeCollection->add('rand_hello', new route('/rand_hello/{name}', array(
      'name' => 'world',
      '_controller' => function($name) { 
	return 'Hello ' . $name . ' ' . rand();
      }
    )));

    $context = new RequestContext();
    $matcher = new UrlMatcher($routeCollection, $context);
    parent::__construct($matcher);
  }
}
