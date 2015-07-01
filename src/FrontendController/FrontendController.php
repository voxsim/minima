<?php

namespace Minima\FrontendController;

use Minima\Provider\LoggerProvider;
use Minima\Util\Stringify;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RouteCollection;

class FrontendController extends UrlMatcher implements FrontendControllerInterface
{
    public function __construct(RouteCollection $routes, RequestContext $requestContext, LoggerInterface $logger = null)
    {
        parent::__construct($routes, $requestContext);
        $this->logger = $logger == null ? new NullLogger() : $logger;
    }

    public function lookup(Request $request)
    {
        try {
            $this->context->fromRequest($request);

            $parameters = $this->match($request->getPathInfo());

            $this->logger->info(sprintf('Matched route "%s" (parameters: %s)', $parameters['_route'], Stringify::parametersToString($parameters)));

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

    public function add($name, Route $route) {
        $this->routes->add($name, $route);
    }

    public static function build(array $configuration) {
        $logger = LoggerProvider::build($configuration);
        $requestContext = new RequestContext();
        $routeCollection = new RouteCollection();
        return new FrontendController($routeCollection, $requestContext, $logger);
    }
}
