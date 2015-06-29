<?php

namespace Minima\Kernel;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Request;

class NullHttpKernel implements HttpKernelInterface
{
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        throw new \RuntimeException('You shoudn\'t call handle from this Null Object, this object was created for compatibility with KernelEvents');
    }
}
