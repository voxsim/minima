<?php namespace Minima;

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
  private $configuration;
  private $httpKernel;

  public function __construct(array $configuration = array())
  {
    $defaultConfiguration = array(
			      'charset' => 'UTF-8',
			      'debug' => false,
			      'twig.path' => __DIR__.'/../views',
			      'cache.path' =>  __DIR__.'/../cache',
			      'cache.page' => 10
			    );
    $this->configuration = array_merge($defaultConfiguration, $configuration);

    $twig = new \Minima\Twig($this->configuration);
    $routes = new \Minima\Routing\ApplicationRouteCollection($twig);

    $context = new Routing\RequestContext();
    $matcher = new Routing\Matcher\UrlMatcher($routes, $context);
    $resolver = new HttpKernel\Controller\ControllerResolver();

    $dispatcher = new EventDispatcher();
    $dispatcher->addSubscriber(new \Minima\Routing\StringToResponseListener);
    $dispatcher->addSubscriber(new HttpKernel\EventListener\RouterListener($matcher));
    $dispatcher->addSubscriber(new HttpKernel\EventListener\ResponseListener($this->configuration['charset']));

    $this->httpKernel = new HttpKernel\HttpKernel($dispatcher, $resolver);

    if(!$this->configuration['debug']) {
      $errorHandler = function (HttpKernel\Exception\FlattenException $exception) {
	$msg = 'Something went wrong! ('.$exception->getMessage().')';
     
	return new Response($msg, $exception->getStatusCode());
      };
      $dispatcher->addSubscriber(new HttpKernel\EventListener\ExceptionListener($errorHandler));

      $this->httpKernel = new HttpCache($this->httpKernel, new Store($this->configuration['cache.path']));
      $dispatcher->addSubscriber(new \Minima\Cache\SetTtlListener($this->configuration['cache.page']));
    }
  }

  public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
  {
    return $this->httpKernel->handle($request, $type, $catch);
  }
}
