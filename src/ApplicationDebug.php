<?php namespace Minima;

use Symfony\Component\Routing;
use Symfony\Component\HttpKernel;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
 
class ApplicationDebug implements HttpKernelInterface 
{
  protected $configuration;
  protected $httpKernel;

  public function __construct(array $configuration, EventDispatcher $dispatcher, ControllerResolver $resolver)
  {
    $defaultConfiguration = array(
			      'charset' => 'UTF-8',
			      'debug' => false,
			      'twig.path' => __DIR__.'/../views',
			      'cache.path' =>  __DIR__.'/../cache',
			      'cache.page' => 10,
			      'log.level' => 'debug',
			      'log.file' => __DIR__ . '/../minima.log'
			    );
    $this->configuration = array_merge($defaultConfiguration, $configuration);

    $twig = new \Minima\Twig($this->configuration);
    $routes = new \Minima\Routing\ApplicationRouteCollection($twig);

    $context = new Routing\RequestContext();
    $matcher = new Routing\Matcher\UrlMatcher($routes, $context);

    $dispatcher->addSubscriber(new \Minima\Routing\StringToResponseListener);
    $dispatcher->addSubscriber(new HttpKernel\EventListener\RouterListener($matcher));
    $dispatcher->addSubscriber(new HttpKernel\EventListener\ResponseListener($this->configuration['charset']));
    $dispatcher->addSubscriber(new \Minima\Logging\LogListener(new \Minima\Logging\Logger($this->configuration)));
 
    $this->httpKernel = new HttpKernel\HttpKernel($dispatcher, $resolver);
  }

  public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
  {
    return $this->httpKernel->handle($request, $type, $catch);
  }
}
