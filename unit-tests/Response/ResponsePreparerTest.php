<?php

use Minima\Response\ResponsePreparer;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ResponsePreparerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->dispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')->getMock();
        $this->response = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')->getMock();

        $this->responsePreparer = new ResponsePreparer($this->dispatcher);
    }

    public function testPrepare()
    {
        $this->dispatcher->expects($this->once())->method('dispatch')->with(KernelEvents::RESPONSE, $this->anything());

        $this->responsePreparer->prepare(new Response(), new Request(), null);
    }

    public function testPrepareWithInvalidResponse()
    {
        try {
            $this->dispatcher->expects($this->once())->method('dispatch')->with(KernelEvents::VIEW, $this->anything());

            $this->responsePreparer->prepare('invalid-response', new Request(), null);
        } catch (LogicException $e) {
            $this->assertEquals('The controller must return a response (invalid-response given).', $e->getMessage());
        }
    }

    public function testPrepareWithNullResponse()
    {
        try {
            $this->dispatcher->expects($this->once())->method('dispatch')->with(KernelEvents::VIEW, $this->anything());

            $this->responsePreparer->prepare(null, new Request(), null);
        } catch (LogicException $e) {
            $this->assertEquals('The controller must return a response (null given). Did you forget to add a return statement somewhere in your controller?', $e->getMessage());
        }
    }
}
