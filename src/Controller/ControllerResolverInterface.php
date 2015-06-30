<?php

namespace Minima\Controller;

interface ControllerResolverInterface
{
    public function getController($controller);

    public function getArguments($controller, array $attributes);
}
