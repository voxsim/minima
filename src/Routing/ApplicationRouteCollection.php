<?php namespace Minima\Routing;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

class ApplicationRouteCollection extends RouteCollection {
  public function __construct($configuration) {
    $this->add('hello', new route('/hello/{name}', array(
      'name' => 'world',
      '_controller' => function($name) { 
	return 'Hello ' . $name;
      }
    )));
    
    $this->add('twig_hello', new Route('/twig_hello/{name}', array(
      'name' => 'World',
      '_controller' => function ($name) use($configuration) {
	$twig = \Minima\Twig::create($configuration);
	return $twig->render('hello.twig', array('name' => $name));
      }
    )));

    $this->add('rand_hello', new route('/rand_hello/{name}', array(
      'name' => 'world',
      '_controller' => function($name) { 
	return 'Hello ' . $name . ' ' . rand();
      }
    )));
  }
}
