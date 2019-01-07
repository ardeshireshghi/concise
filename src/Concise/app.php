<?php

namespace Concise;

use function Concise\Http\Request\url;
use function Concise\Http\Request\rawPath;
use function Concise\Http\Adapter\request as requestAdapter;
use function Concise\Http\Response\response;
use function Concise\Http\Response\statusCode;
use function Concise\Http\Response\send;
use function Concise\Http\Session\middleware as sessionMiddleware;
use function Concise\FP\compose;
use function Concise\FP\curry;
use function Concise\FP\reduce;
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

function createWrappedRouteHandlerInvoker(array $middlewares = [])
{
  return function ($matchingRoutes) use ($middlewares) {
    // Pick the first matching route
    $routeHandlerWrapped = (count($middlewares) === 0) ?
  current($matchingRoutes)['handler'] :
    reduce(function ($handlerWrapped, $currentMiddleware) {
      return $currentMiddleware($handlerWrapped)([]);
    }, current($matchingRoutes)['handler'], array_reverse($middlewares));

    $handlerWithSessionMiddleware = handlerWithDefaultMiddlewares($routeHandlerWrapped);
    return $handlerWithSessionMiddleware(requestAdapter(current($matchingRoutes)));
  };
}

function createRouteNotFoundHandler(array $middlewares = [])
{
  $notFoundHandler = function () {
    return send(response('Route for path: "'.rawPath().'" not found')(statusCode(404, [])));
  };

  return (count($middlewares) === 0) ?
  $notFoundHandler :
  reduce(function ($handlerWrapped, $currentMiddleware) {
    return $currentMiddleware($handlerWrapped)([]);
  }, $notFoundHandler, array_reverse($middlewares));
}

function createRouteHandler(array $middlewares = [])
{
  return function ($matchingRoutes) use ($middlewares) {
    return ifElse(
  createMatchRouteChecker(),
  createWrappedRouteHandlerInvoker($middlewares),
  createRouteNotFoundHandler($middlewares)
  )($matchingRoutes);
  };
}

function createApp()
{
  return function (array $routes, array $middlewares = []) {
    return compose(createRouteHandler($middlewares), createRouteMatcherFilter(), 'array_values')($routes);
  };
}

function app(array $routes = [], ...$thisArgs)
{
  return curry(createApp())($routes, ...$thisArgs);
};
