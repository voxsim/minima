<?php

namespace Minima\Event;

use Minima\Kernel\NullHttpKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class GetResponseEvent extends \Symfony\Component\HttpKernel\Event\GetResponseEvent
{
    public function __construct(Request $request)
    {
        parent::__construct(new NullHttpKernel(), $request, HttpKernelInterface::MASTER_REQUEST);
    }
}
