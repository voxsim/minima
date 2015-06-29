<?php

namespace Minima\Event;

use Minima\Kernel\NullHttpKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class GetResponseForExceptionEvent extends \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent
{
    public function __construct(Request $request, \Exception $e)
    {
        parent::__construct(new NullHttpKernel(), $request, HttpKernelInterface::MASTER_REQUEST, $e);
    }
}
