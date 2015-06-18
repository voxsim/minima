<?php

namespace Minima\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

interface ControllerResolverInterface
{
    public function resolve(Request $request, $type, HttpKernelInterface $httpKernel);
}
