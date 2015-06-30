<?php

use Minima\Http\Request;

class DebugApplicationIntegrationTest extends ApplicationIntegrationTest
{
    protected function getDebugFlag()
    {
        return true;
    }

    public function testNotFoundHandling()
    {
        try {
            $response = $this->application->handle(Request::create('/invalid-url'));
            throw new \RuntimeException('Application in debug mode should throws NotFoundHttpException');
        } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            $this->assertTrue(true);
        }
    }
}
