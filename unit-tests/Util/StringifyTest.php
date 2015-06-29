<?php

use Minima\Util\Stringify;

class StringifyTest extends \PHPUnit_Framework_TestCase
{
    public function testObjectToString()
    {
        $this->assertEquals('Object(stdClass)', Stringify::varToString(new StdClass()));
    }

    public function testEmptyArrayToString()
    {
        $this->assertEquals('Array()', Stringify::varToString(array()));
    }

    public function testArrayToString()
    {
        $this->assertEquals('Array(key => value, key2 => value2)', Stringify::varToString(array('key' => 'value', 'key2' => 'value2')));
    }

    public function testNullToString()
    {
        $this->assertEquals('null', Stringify::varToString(null));
    }

    public function testFalseToString()
    {
        $this->assertEquals('false', Stringify::varToString(false));
    }

    public function testTrueToString()
    {
        $this->assertEquals('true', Stringify::varToString(true));
    }

    public function testStringToString()
    {
        $this->assertEquals('a string', Stringify::varToString('a string'));
    }

    public function testResourceToString()
    {
        $resource = tmpfile();
        $this->assertEquals('Resource(stream)', Stringify::varToString($resource));
    }
}
