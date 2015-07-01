<?php

namespace Minima\FrontendController;

use Symfony\Component\HttpFoundation\Request;

class NullFrontendController implements FrontendControllerInterface
{
    public function lookup(Request $request)
    {
    }
}
