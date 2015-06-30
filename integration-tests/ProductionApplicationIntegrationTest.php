<?php

use Minima\Http\Request;

class ProductionApplicationIntegrationTest extends ApplicationIntegrationTest
{
    protected function getDebugFlag()
    {
        return false;
    }

    public function testNotFoundHandling()
    {
        $response = $this->application->handle(Request::create('/invalid-url'));

        $this->assertEquals('Something went wrong! (No route found for "GET /invalid-url")', $response->getContent());
    }
}
