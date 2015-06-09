<?php namespace Minima\Routing;

use Symfony\Component\HttpFoundation\Request;

interface RouterInterface {
  public function lookup(Request $request);
}
