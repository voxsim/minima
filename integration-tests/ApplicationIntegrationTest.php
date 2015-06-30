<?php

use Minima\Auth\Authentication;
use Minima\Builder\TwigBuilder;
use Minima\Http\Request;
use Minima\Http\Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpFoundation\RedirectResponse;

abstract class ApplicationIntegrationTest extends \PHPUnit_Framework_TestCase
{
    protected $application;
    protected $logger;

    public function setUp()
    {
        $this->logger = new TestLogger();

        $this->application = $this->createApplication($this->logger, $this->getDebugFlag());
    }

    abstract protected function getDebugFlag();

    protected function createApplication(LoggerInterface $logger, $debug = true)
    {
        $configuration = array(
            'debug' => $debug,
            'twig.path' => __DIR__.'/views',
            'security.firewalls' => array(
                'guest' => array(
                    'pattern' => '^/login$'
                ),
                'secured' => array(
                    'pattern' => '^.*$',
                    '_controller' => function(Response $response, Authentication $auth) {
                        if (!$auth->check($request)) {
                            return $response->redirect('/login');
                        }
                    },
                    'response' => new Response,
                    'auth' => new Authentication
                )
            )
        );

        $dispatcher = new EventDispatcher();
        $routeCollection = $this->createRouteCollection($configuration, $this->logger);

        return ApplicationFactory::build($dispatcher, $routeCollection, $configuration);
    }

    public function createRouteCollection(array $configuration, LoggerInterface $logger)
    {
        $routeCollection = new RouteCollection();

        $routeCollection->add('hello', new Route('/hello/{name}', array(
            'name' => 'world',
            '_controller' => function ($name) {
                return 'Hello '.$name;
            }
        )));

        $routeCollection->add('twig_hello', new Route('/twig_hello/{name}', array(
            'name' => 'World',
            'twig' => TwigBuilder::build($configuration),
            '_controller' => function ($name, $twig) {
                return $twig->render('hello.twig', array('name' => $name));
            }
        )));

        $routeCollection->add('rand_hello', new Route('/rand_hello/{name}', array(
            'name' => 'world',
            '_controller' => function ($name) {
                return 'Hello '.$name.' '.rand();
            }
        )));

        $routeCollection->add('log_hello', new Route('/log_hello/{name}', array(
            'name' => 'world',
            'logger' => $logger,
            '_controller' => function ($name, $logger) {
                $logger->info('Message from controller');
            }
        )));

        $routeCollection->add('login', new Route('/login', array(
            '_controller' => function (Request $request, Response $response, Authentication $auth) {
                if ($auth->attempt($request)) {
                    return $response->redirect('/account');
                }

                $response->headers->set('WWW-Authenticate', sprintf('Basic realm="%s"', 'site_login'));
                $response->setStatusCode(401, 'Please sign in.');
                return $response;
            },
            'response' => new Response,
            'auth' => new Authentication
        )));

        $routeCollection->add('account', new Route('/account', array(
            '_controller' => function (Request $request, Response $response, Authentication $auth) {
                        if (!$auth->check($request)) {
                            return $response->redirect('/login');
                        }
                        
                $user = $request->getSession()->get('user');
                $response->setContent("Welcome {$user['username']}!");
                return $response;
            },
            'response' => new Response,
            'auth' => new Authentication
        )));

        return $routeCollection;
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

        $this->assertEquals('Hello Simon'."\n", $response->getContent());
    }

    public function testLogging()
    {
        $request = Request::create('/log_hello/Simon');

        $this->application->handle($request);
        $messages = $this->logger->getMessages();

        $this->assertEquals('Message from controller', $messages[0][1]);
    }

    public function testShowMeBasicAuthentication()
    {
        $request = Request::create('/login');

        $response = $this->application->handle($request);

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testRedirectToAccount()
    {
        $request = Request::create('/login');
        $request->server->set('PHP_AUTH_USER', 'Simon');
        $request->server->set('PHP_AUTH_PW', 'password');

        $response = $this->application->handle($request);

        $this->assertEquals(302, $response->getStatusCode());

        $this->assertTrue($this->containsString($response->getContent(), 'Redirecting to /account'));

        $request = Request::create($response->headers->get('Location'), 'GET', array(), array(), array(), array(), null, $request->getSession());

        $response = $this->application->handle($request);

        $this->assertEquals('Welcome Simon!', $response->getContent());
    }

    public function testRedirectToLoginIfNotLogged()
    {
        $request = Request::create('/account');

        $response = $this->application->handle($request);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($this->containsString($response->getContent(), 'Redirecting to /login'));
    }

    private function containsString($string, $substring)
    {
        return strpos($string, $substring) > 0;
    }
}
