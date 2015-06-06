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
  protected $logger;

  public function __construct(array $configuration, EventDispatcher $dispatcher, ControllerResolver $resolver, \Minima\Logging\Logger $logger)
  {
    $this->logger = $logger;
    $this->configuration = $configuration;
    $this->httpKernel = new HttpKernel\HttpKernel($dispatcher, $resolver);

    $twig = new \Minima\Twig($this->configuration);
    $routes = new \Minima\Routing\ApplicationRouteCollection($twig);

    $context = new Routing\RequestContext();
    $matcher = new Routing\Matcher\UrlMatcher($routes, $context);

    $dispatcher->addSubscriber(new \Minima\Routing\StringToResponseListener);
    $dispatcher->addSubscriber(new HttpKernel\EventListener\RouterListener($matcher));
    $dispatcher->addSubscriber(new HttpKernel\EventListener\ResponseListener($this->configuration['charset']));
    $dispatcher->addSubscriber(new \Minima\Logging\LogListener(new \Minima\Logging\Logger($this->configuration)));
 
    $this->logger->info('> Application built');
  }

  public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
  {
    return $this->httpKernel->handle($request, $type, $catch);
  }
}
