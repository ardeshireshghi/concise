<?php

namespace SuperServer\RouteMatcher;

use function SuperServer\Http\Request\path;
use function SuperServer\Http\Request\method as reqMethod;
use function SuperServer\Routing\routeSegments;
use function SuperServer\Routing\matchRouteAgainstPath;
use function SuperServer\FP\compose;
use function SuperServer\FP\filter;
use function SuperServer\FP\allPass;
use function SuperServer\FP\somePass;

const REGEX_MIN_ARRAY_COUNT_WHEN_MATCH = 2;

function routeWithNoParamMatchesPath($route, $regexResultMatches) {
  return allPass(
    count($route['params']) === 0,
    count($regexResultMatches) === 1,
    count(explode('/', path())) <= count(routeSegments($route))
  );
}

function routeWithParamsMatchesPath($route, $regexResultMatches) {
  return (count($regexResultMatches) >= REGEX_MIN_ARRAY_COUNT_WHEN_MATCH + count($route['params']));
}

function createRouteMatcherFilter() {
  return filter(function($route) {
    $regexResultMatches = matchRouteAgainstPath($route, path());

    return allPass(
      $route['method'] === reqMethod(),
      somePass(
        routeWithNoParamMatchesPath($route, $regexResultMatches),
        routeWithParamsMatchesPath($route, $regexResultMatches)
      )
    );
  });
}
