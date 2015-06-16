<?php namespace Minima\Response;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface ResponsePreparerInterface {
  public function validateAndPrepare($response, Request $request, $type, HttpKernelInterface $httpKernel);
  public function prepare(Response $response, Request $request, $type, HttpKernelInterface $httpKernel);
}
