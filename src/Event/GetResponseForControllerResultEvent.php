<?php

namespace Minima\Event;

use Minima\Kernel\NullHttpKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class GetResponseForControllerResultEvent extends \Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent
{
    public function __construct(Request $request, $response)
    {
        parent::__construct(new NullHttpKernel(), $request, HttpKernelInterface::MASTER_REQUEST, $response);
    }
}
