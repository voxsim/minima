<?php

use Symfony\Component\Routing;
use Symfony\Component\HttpKernel;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Response;
 
class Application extends HttpKernel\HttpKernel
{
    public function __construct($routes, $dispatcher, $debug)
    {
        $context = new Routing\RequestContext();
        $matcher = new Routing\Matcher\UrlMatcher($routes, $context);
        $resolver = new HttpKernel\Controller\ControllerResolver();
 
        $dispatcher->addSubscriber(new \Routing\Listener\StringToResponseListener);
        $dispatcher->addSubscriber(new HttpKernel\EventListener\RouterListener($matcher));
        $dispatcher->addSubscriber(new HttpKernel\EventListener\ResponseListener('UTF-8'));

	if($debug) {
	  $errorHandler = function (HttpKernel\Exception\FlattenException $exception) {
	      $msg = 'Something went wrong! ('.$exception->getMessage().')';
	   
	      return new Response($msg, $exception->getStatusCode());
	  };
	  $dispatcher->addSubscriber(new HttpKernel\EventListener\ExceptionListener($errorHandler));
	}
 
        parent::__construct($dispatcher, $resolver);
    }
}
