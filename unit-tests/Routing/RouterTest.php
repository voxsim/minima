<?php

use Minima\Routing\Router;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();
        $this->matcher = $this->getMockBuilder('Symfony\Component\Routing\Matcher\UrlMatcherInterface')->getMock();
        $this->request = Request::create('path');

        $this->router = new Router($this->matcher, $this->logger);
    }

    public function testLookup()
    {
        $this->matcher->expects($this->once())->method('match')->with('path')->willReturn(
            array('_route' => 'path', '_controller' => 'myController', 'other' => 'value')
        );
        $this->logger->expects($this->once())->method('info')->with('Matched route "path" (parameters: "_route": "path", "_controller": "myController", "other": "value")');

        $this->assertEquals(new ParameterBag(), $this->request->attributes);

        $this->router->lookup($this->request);

        $this->assertEquals(new ParameterBag(array(
            '_route' => 'path',
            '_controller' => 'myController',
            'other' => 'value',
            '_route_params' => array('other' => 'value'),
        )), $this->request->attributes);
    }

    public function testResourceNotFoundException()
    {
        try {
            $this->matcher->expects($this->once())->method('match')->with('path')->willThrowException(new ResourceNotFoundException());

            $this->router->lookup($this->request);
        } catch (NotFoundHttpException $e) {
            $this->assertEquals('No route found for "GET path"', $e->getMessage());
        }
    }

    public function testResourceNotFoundExceptionWithReferer()
    {
        try {
            $this->matcher->expects($this->once())->method('match')->with('path')->willThrowException(new ResourceNotFoundException());

            $this->request->headers->set('referer', 'myReferer');
            $this->router->lookup($this->request);
        } catch (NotFoundHttpException $e) {
            $this->assertEquals('No route found for "GET path" (from "myReferer")', $e->getMessage());
        }
    }

    public function testMethodNotAllowedException()
    {
        try {
            $this->matcher->expects($this->once())->method('match')->with('path')->willThrowException(new MethodNotAllowedException(array('POST')));

            $this->router->lookup($this->request);
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertEquals('No route found for "GET path": Method Not Allowed (Allow: POST)', $e->getMessage());
        }
    }
}
