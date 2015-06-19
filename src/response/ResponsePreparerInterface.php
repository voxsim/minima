<?php namespace Minima\Response;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface ResponsePreparerInterface {
  public function validateAndPrepare($response, Request $request);
  public function prepare(Response $response, Request $request);
}
