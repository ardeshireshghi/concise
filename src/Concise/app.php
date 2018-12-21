<?php

namespace Concise;

use function Concise\Http\Request\url;
use function Concise\Http\Request\path;
use function Concise\Http\Request\parseRouteParamsFromPath;
use function Concise\Http\Response\response;
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
    return response('Route for path: \"'.path().'\" not found');
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

function app($routes)
{
  return compose(createRouteHandler(), createRouteMatcherFilter(), 'array_values')($routes);
};
