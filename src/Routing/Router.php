<?php namespace Minima\Routing;

use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class Router {
  private $matcher;

  public function __construct($configuration, LoggerInterface $logger = null) {
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
	$twig = \Minima\Twig::create($configuration);
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

    $context = new RequestContext();
    $this->matcher = new UrlMatcher($routeCollection, $context);
    $this->logger = $logger;
  }

  public function lookup(Request $request) {
    if ($request->attributes->has('_controller')) {
	// routing is already done
	return;
    }

    // add attributes based on the request (routing)
    try {
	// matching a request is more powerful than matching a URL path + context, so try that first
	if ($this->matcher instanceof RequestMatcherInterface) {
	    $parameters = $this->matcher->matchRequest($request);
	} else {
	    $parameters = $this->matcher->match($request->getPathInfo());
	}

	if (null !== $this->logger) {
	    $this->logger->info(sprintf('Matched route "%s" (parameters: %s)', $parameters['_route'], $this->parametersToString($parameters)));
	}

	$request->attributes->add($parameters);
	unset($parameters['_route'], $parameters['_controller']);
	$request->attributes->set('_route_params', $parameters);
    } catch (ResourceNotFoundException $e) {
	$message = sprintf('No route found for "%s %s"', $request->getMethod(), $request->getPathInfo());

	if ($referer = $request->headers->get('referer')) {
	    $message .= sprintf(' (from "%s")', $referer);
	}

	throw new NotFoundHttpException($message, $e);
    } catch (MethodNotAllowedException $e) {
	$message = sprintf('No route found for "%s %s": Method Not Allowed (Allow: %s)', $request->getMethod(), $request->getPathInfo(), implode(', ', $e->getAllowedMethods()));

	throw new MethodNotAllowedHttpException($e->getAllowedMethods(), $message, $e);
    }
  }

  private function parametersToString(array $parameters)
  {
      $pieces = array();
      foreach ($parameters as $key => $val) {
	  $pieces[] = sprintf('"%s": "%s"', $key, (is_string($val) ? $val : json_encode($val)));
      }

      return implode(', ', $pieces);
  }
}
