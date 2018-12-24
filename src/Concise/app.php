<?php

namespace Concise;

use function Concise\Http\Request\url;
use function Concise\Http\Request\rawPath;
use function Concise\Http\Request\parseRouteParamsFromPath;
use function Concise\Http\Response\response;
use function Concise\Http\Response\statusCode;
use function Concise\Http\Response\send;
use function Concise\Http\Session\middleware as sessionMiddleware;
use function Concise\FP\compose;
use function Concise\FP\ifElse;
use function Concise\RouteMatcher\createRouteMatcherFilter;

function createMatchRouteChecker()
{
  return function ($matchingRoutes) {
    return count($matchingRoutes) > 0;
  };
}

function handlerWithDefaultMiddlewares($routeHandler)
{
  $sessionMiddlewareParams = [];
  return sessionMiddleware($routeHandler)($sessionMiddlewareParams);
}

function createRouteHandlerInvoker()
{
  return function ($matchingRoutes) {
    // Pick the first matching route
    $handlerWithSessionMiddleware = handlerWithDefaultMiddlewares(current($matchingRoutes)['handler']);
    return $handlerWithSessionMiddleware(parseRouteParamsFromPath(current($matchingRoutes)));
  };
}

function createRouteNotFoundHandler()
{
  return function () {
    return send(response('Route for path: "'.rawPath().'" not found')(statusCode(404, [])));
  };
}

function createRouteHandler()
{
  return function ($matchingRoutes) {
    return ifElse(
  createMatchRouteChecker(),
  createRouteHandlerInvoker(),
  createRouteNotFoundHandler()
  )($matchingRoutes);
  };
}

function app(array $routes = [])
{
  $app = compose(createRouteHandler(), createRouteMatcherFilter(), 'array_values');
  return count($routes) > 0 ? $app($routes) : $app;
};
