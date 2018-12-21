<?php

namespace SuperServer;

use function SuperServer\Http\Request\url;
use function SuperServer\Http\Request\path;
use function SuperServer\Http\Request\parseRouteParamsFromPath;
use function SuperServer\Http\Response\response;
use function SuperServer\Http\Session\middleware as sessionMiddleware;
use function SuperServer\FP\compose;
use function SuperServer\FP\ifElse;
use function SuperServer\RouteMatcher\createRouteMatcherFilter;

function createMatchRouteChecker() {
  return function($matchingRoutes) {
    return count($matchingRoutes) > 0;
  };
}

function handlerWithDefaultMiddlewares($routeHandler) {
  return sessionMiddleware([])($routeHandler);
}

function createRouteHandlerInvoker() {
  return function($matchingRoutes) {
    // Pick the first matching route
    $handlerWithSessionMiddleware = handlerWithDefaultMiddlewares(current($matchingRoutes)['handler']);
    return $handlerWithSessionMiddleware(parseRouteParamsFromPath(current($matchingRoutes)));
  };
}

function createRouteNotFoundHandler() {
  return function() {
    return response('Route for path: \"'.path().'\" not found');
  };
}

function createRouteHandler() {
  return function($matchingRoutes) {
    return ifElse(
      createMatchRouteChecker(),
      createRouteHandlerInvoker(),
      createRouteNotFoundHandler()
    )($matchingRoutes);
  };
}


function app($routes) {
  return compose(
    createRouteHandler(),
    createRouteMatcherFilter(),
    'array_values'
  )($routes);
};
