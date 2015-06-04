<?php namespace Routing;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

class ApplicationRouteCollection extends RouteCollection {
  public function __construct(\Twig_Environment $twig) {
  }
}
