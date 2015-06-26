<?php

use Minima\Builder\TwigBuilder;
use Psr\Log\LoggerInterface;
use Minima\Security\NativeSessionTokenStorage;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

abstract class ApplicationIntegrationTest extends \PHPUnit_Framework_TestCase {
  protected $application;
  protected $logger;

  public static function setUpBeforeClass()
  {
    ini_set('session.save_handler', 'files');
    ini_set('session.save_path', sys_get_temp_dir());

    parent::setUpBeforeClass();
  }

  public function setUp()
  {
    $_SESSION = array();

    $this->logger = new TestLogger();

    $this->application = $this->createApplication($this->logger, $this->getDebugFlag());
  }
  
  abstract protected function getDebugFlag();

  protected function createApplication(LoggerInterface $logger, $debug = true)
  {
    $configuration = array(
			  'debug' => $debug,
			  'twig.path' => __DIR__.'/views',
			  'cache.path' =>  __DIR__.'/cache',
			  'security.firewalls' => array(
			    'admin' => array(
			      'pattern' => '^/secured_hello',
			      'http' => true,
			      'users' => array(
				// raw password is foo
				'Simon' => array('ROLE_ADMIN', '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg=='),
			      )
			    )
			  )
			);

    $dispatcher = new EventDispatcher();
    $tokenStorage = new TokenStorage();
    $routeCollection = $this->createRouteCollection($configuration, $this->logger);
    return ApplicationFactory::build($dispatcher, $routeCollection, $configuration, $tokenStorage);
  }

  public function createRouteCollection(array $configuration, LoggerInterface $logger) {
    $routeCollection = new RouteCollection();

    $routeCollection->add('hello', new route('/hello/{name}', array(
      'name' => 'world',
      '_controller' => function($name) { 
	return 'Hello ' . $name;
      }
    )));
    
    $routeCollection->add('twig_hello', new Route('/twig_hello/{name}', array(
      'name' => 'World',
      '_controller' => function ($name) use($configuration) {
	$twig = TwigBuilder::build($configuration);
	return $twig->render('hello.twig', array('name' => $name));
      }
    )));

    $routeCollection->add('rand_hello', new route('/rand_hello/{name}', array(
      'name' => 'world',
      '_controller' => function($name) { 
	return 'Hello ' . $name . ' ' . rand();
      }
    )));

    $routeCollection->add('log_hello', new route('/log_hello/{name}', array(
      'name' => 'world',
      '_controller' => function($name) use($logger) {
        $logger->info('Message from controller'); 
      }
    )));

    return $routeCollection;
  }
}
