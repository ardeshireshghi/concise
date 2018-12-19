<?php

namespace SuperServer;

use function SuperServer\Http\Request\url;
use function SuperServer\Http\Request\path;
use function SuperServer\Http\Request\parseRouteParamsFromPath;
use function SuperServer\Http\Response\response;
use function SuperServer\FP\compose;
use function SuperServer\FP\ifElse;
use function SuperServer\RouteMatcher\createRouteMatcherFilter;

function createMatchRouteChecker() {
  return function($matchingRoutes) {
    return count($matchingRoutes) > 0;
  };
}

function createRouteHandlerInvoker() {
  return function($matchingRoutes) {
    // Pick the first matching route
    $selectedRoute = current($matchingRoutes);
    $selectedRoute['handler'](parseRouteParamsFromPath($selectedRoute));
  };
}

function createRouteNotFoundHandler() {
  return function() {
    response('Route for path: \"'.path().'\" not found');
  };
}

function createRouteHandler() {
  return function ($matchingRoutes) {
    ifElse(
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
