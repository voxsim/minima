<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

class ProductionApplicationIntegrationTest extends ApplicationIntegrationTest
{
    protected function getDebugFlag()
    {
        return false;
    }

    public function testNotFoundHandling()
    {
        $response = $this->application->handle(new Request());

        $this->assertEquals('Something went wrong! (No route found for "GET /")', $response->getContent());
    }
}
