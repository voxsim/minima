<?php namespace Minima\Http;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

class Request extends \Symfony\Component\HttpFoundation\Request {

  public static function createFromGlobals() {
    $request = parent::createFromGlobals();

    $storage = new NativeSessionStorage();
    $session = new Session($storage);

    $request->setSession($session);

    return $request;
  }

  public static function create($uri, $method = 'GET', $parameters = array(), $cookies = array(), $files = array(), $server = array(), $content = null, $session = null) {
    $request = parent::create($uri, $method, $parameters, $cookies, $files, $server, $content);

    if($session == null) {
      $storage = new MockFileSessionStorage();
      $session = new Session($storage);
    }

    $request->setSession($session);

    return $request;
  }
}
