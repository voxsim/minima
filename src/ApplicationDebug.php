<?php namespace Minima;

use Symfony\Component\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpFoundation\Request;
 
class ApplicationDebug implements HttpKernelInterface 
{
  protected $configuration;
  protected $httpKernel;

  public function __construct(array $configuration, EventDispatcherInterface $dispatcher, ControllerResolverInterface $resolver)
  {
    $defaultConfiguration = array('charset' => 'UTF-8');
    $configuration = array_merge($defaultConfiguration, $configuration);

    $this->configuration = $configuration;
    $this->httpKernel = new HttpKernel\HttpKernel($dispatcher, $resolver);

    $dispatcher->addSubscriber(new HttpKernel\EventListener\ResponseListener($this->configuration['charset']));
    $dispatcher->addSubscriber(new \Minima\Routing\StringToResponseListener);
  }

  public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
  {
    return $this->httpKernel->handle($request, $type, $catch);
  }
}
