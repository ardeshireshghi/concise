<?php

namespace Concise\RouteMatcher;

use function Concise\Http\Request\path;
use function Concise\Http\Request\method as reqMethod;
use function Concise\Routing\routeSegments;
use function Concise\Routing\matchRouteAgainstPath;
use function Concise\FP\compose;
use function Concise\FP\filter;
use function Concise\FP\allPass;
use function Concise\FP\somePass;

const REGEX_MIN_ARRAY_COUNT_WHEN_MATCH = 2;

function routeWithNoParamMatchesPath($route, $regexResultMatches)
{
  return allPass(
  count($route['params']) === 0,
  count($regexResultMatches) === 1,
  count(explode('/', path())) <= count(routeSegments($route))
  );
}

function routeWithParamsMatchesPath($route, $regexResultMatches)
{
  return (count($regexResultMatches) >= REGEX_MIN_ARRAY_COUNT_WHEN_MATCH + count($route['params']));
}

function createRouteMatcherFilter()
{
  return filter(function ($route) {
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
