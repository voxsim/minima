<?php namespace Minima\Event;

use Minima\Kernel\NullHttpKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class PostResponseEvent extends \Symfony\Component\HttpKernel\Event\PostResponseEvent {
  public function __construct(Request $request, Response $response) {
    parent::__construct(new NullHttpKernel(), $request, $response);
  } 
}
