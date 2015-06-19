<?php

namespace Minima\Controller;

use Symfony\Component\HttpFoundation\Request;

interface ControllerResolverInterface
{
    public function resolve(Request $request);
}
