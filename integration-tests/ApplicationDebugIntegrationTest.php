<?php

require_once __DIR__.'/../vendor/autoload.php';

use Minima\Builder\TwigBuilder;
use Minima\Event\EventDispatcher;
use Minima\Security\NativeSessionTokenStorage;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ApplicationDebugIntegrationTest extends \PHPUnit_Framework_TestCase {
  const SESSION_NAMESPACE = 'minima';

  private $application;
  private $logger;

  public static function setUpBeforeClass()
  {
    ini_set('session.save_handler', 'files');
    ini_set('session.save_path', sys_get_temp_dir());

    parent::setUpBeforeClass();
  }

  protected function setUp()
  {
    $_SESSION = array();

    $this->storage = new NativeSessionTokenStorage(self::SESSION_NAMESPACE);
    $this->logger = new TestLogger();
    $this->application = $this->createApplication($this->logger, $this->storage);
  }

  public function testNotFoundHandling()
  {
    try {
      $response = $this->application->handle(new Request());
      throw new \RuntimeException("Application, in debug mode, should throw NotFoundHttpException");
    } catch(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
      $this->assertTrue(true);
    }
  }
  
  public function testRoute()
  {
    $request = Request::create('/hello/Simon');

    $response = $this->application->handle($request);

    $this->assertEquals('Hello Simon', $response->getContent());
  }

  public function testTwig()
  {
    $request = Request::create('/twig_hello/Simon');

    $response = $this->application->handle($request);

    $this->assertEquals('Hello Simon' . "\n", $response->getContent());
  }
  
  public function testCaching()
  {
    $request = Request::create('/rand_hello/Simon');

    $response1 = $this->application->handle($request);
    $response2 = $this->application->handle($request);

    $this->assertNotEquals($response1->getContent(), $response2->getContent());
  }
  
  public function testLogging()
  {
    $request = Request::create('/log_hello/Simon');

    $this->application->handle($request);
    $messages = $this->logger->getMessages();

    $this->assertEquals('Message from controller', $messages[0][1]);
  }
  
  public function testUnsecuredPath()
  {
    $request = Request::create('/unsecured_hello');

    $response = $this->application->handle($request);

    $this->assertEquals('Hello anonymous access', $response->getContent());
  }

  public function testSecuredPath()
  {
    $request = Request::create('/secured_hello');
    $request->headers->set('PHP_AUTH_USER', 'Simon');
    $request->headers->set('PHP_AUTH_PW', 'foo');

    $response = $this->application->handle($request);

    $this->assertEquals('Hello Simon', $response->getContent());
  }

  public function testBlockUnknownUserForSecuredPath()
  {
    $request = Request::create('/secured_hello');

    $response = $this->application->handle($request);

    $this->assertEquals('401', $response->getStatusCode());
  }

  private function createApplication(LoggerInterface $logger, TokenStorageInterface $tokenStorage)
  {
    $configuration = array(
      'debug' => true,
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
    $routeCollection = $this->createRouteCollection($configuration, $this->logger, $tokenStorage);
    return ApplicationFactory::build($dispatcher, $routeCollection, $configuration, $tokenStorage);
  }

  public function createRouteCollection(array $configuration, LoggerInterface $logger, TokenStorageInterface $tokenStorage) {
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
    
    $routeCollection->add('secured_hello', new route('/secured_hello', array(
      '_controller' => function() {
	$tokenStorage = new NativeSessionTokenStorage('minima');
	$token = $tokenStorage->getToken();
	$name = 'unknown user';

	if (null !== $token) {
	    $user = $token->getUser();
	    $name = $user->getUsername();	    	    
	}

	return 'Hello ' . $name;
      }
    )));

    $routeCollection->add('unsecured_hello', new route('/unsecured_hello', array(
      '_controller' => function() {
	return 'Hello anonymous access';
      }
    )));

    return $routeCollection;
  }
}
