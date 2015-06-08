<?php namespace Minima;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;
use Symfony\Component\HttpKernel\HttpCache\Store;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener;
use Symfony\Component\HttpKernel\Exception\FlattenException;
 
class Application extends ApplicationDebug 
{
  public function __construct(array $configuration, EventDispatcher $dispatcher, ControllerResolver $resolver, \Minima\Routing\Router $router, LoggerInterface $logger)
  {
    $defaultConfiguration = array(
			      'cache.path' =>  __DIR__.'/../cache',
			      'cache.page' => 10,
			    );
    $configuration = array_merge($defaultConfiguration, $configuration);

    parent::__construct($configuration, $dispatcher, $resolver, $router, $logger);

    $errorHandler = function (FlattenException $exception) {
      $msg = 'Something went wrong! ('.$exception->getMessage().')';
   
      return new Response($msg, $exception->getStatusCode());
    };
    $dispatcher->addSubscriber(new ExceptionListener($errorHandler));

    $this->httpKernel = new HttpCache($this->httpKernel, new Store($this->configuration['cache.path']));
    $dispatcher->addSubscriber(new \Minima\Cache\SetTtlListener($this->configuration['cache.page']));
  }

  public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
  {
    return $this->httpKernel->handle($request, $type, $catch);
  }
}
