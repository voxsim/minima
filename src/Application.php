<?php

use Symfony\Component\Routing;
use Symfony\Component\HttpKernel;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;
use Symfony\Component\HttpKernel\HttpCache\Store;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
 
class Application implements HttpKernelInterface 
{
    private $httpKernel;

    public function __construct(array $configuration)
    {
	$twig = new Twig($configuration);
	$routes = new \Routing\ApplicationRouteCollection($twig);

        $context = new Routing\RequestContext();
        $matcher = new Routing\Matcher\UrlMatcher($routes, $context);
        $resolver = new HttpKernel\Controller\ControllerResolver();
 
	$dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new \Routing\StringToResponseListener);
        $dispatcher->addSubscriber(new HttpKernel\EventListener\RouterListener($matcher));
        $dispatcher->addSubscriber(new HttpKernel\EventListener\ResponseListener($configuration['charset']));

        $this->httpKernel = new HttpKernel\HttpKernel($dispatcher, $resolver);

	if(!$configuration['debug']) {
	  $errorHandler = function (HttpKernel\Exception\FlattenException $exception) {
	      $msg = 'Something went wrong! ('.$exception->getMessage().')';
	   
	      return new Response($msg, $exception->getStatusCode());
	  };
	  $dispatcher->addSubscriber(new HttpKernel\EventListener\ExceptionListener($errorHandler));

	  $this->httpKernel = new HttpCache($this->httpKernel, new Store($configuration['cache.path']));
          $dispatcher->addSubscriber(new \Cache\SetTtlListener($configuration['cache.page']));
	}
    }
 
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
	return $this->httpKernel->handle($request, $type, $catch);
    }
}
