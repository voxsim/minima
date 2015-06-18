<?php namespace Minima\Routing;

use Symfony\Component\HttpFoundation\Request;

class NullRouter implements RouterInterface {
  public function lookup(Request $request) {
  }
}
