<?php

namespace Minima\Event;

use Minima\Kernel\NullHttpKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class FilterControllerEvent extends \Symfony\Component\HttpKernel\Event\FilterControllerEvent
{
    public function __construct($controller, Request $request)
    {
        parent::__construct(new NullHttpKernel(), $controller, $request, HttpKernelInterface::MASTER_REQUEST);
    }
}
