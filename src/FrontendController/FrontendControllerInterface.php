<?php

namespace Minima\FrontendController;

use Symfony\Component\HttpFoundation\Request;

interface FrontendControllerInterface
{
    public function lookup(Request $request);
}
