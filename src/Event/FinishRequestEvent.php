<?php

namespace Minima\Event;

use Minima\Kernel\NullHttpKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class FinishRequestEvent extends \Symfony\Component\HttpKernel\Event\FinishRequestEvent
{
    public function __construct(Request $request)
    {
        parent::__construct(new NullHttpKernel(), $request, HttpKernelInterface::MASTER_REQUEST);
    }
}
