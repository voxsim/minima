<?php namespace Minima\Response;

use Symfony\Component\HttpFoundation\Request;

interface ResponsePreparerInterface {
  public function prepare($response, Request $request);
}
