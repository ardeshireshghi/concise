<?php

namespace Concise\Routing;

use function Concise\FP\curry;
use function Concise\FP\reduce;
use function Concise\FP\map;

function routeParamsPattern()
{
  return '/:([\w\_]+)/';
}

function namedCapturingGroupPattern()
{
  return '(?<$1>[\w\-]+)';
}

function routeSlashPattern()
{
  return '/\//';
}

function routeWrapPattern()
{
  return '/^(.+)$/';
}

function routePatternsToFind()
{
  return [
    routeParamsPattern(),
    routeSlashPattern(),
    routeWrapPattern()
  ];
}

function routeReplacePatterns()
{
  return [
    namedCapturingGroupPattern(),
    '\/',
    '/$1/'
  ];
}

function createRegexFromPattern($routePattern)
{
  return preg_replace(routePatternsToFind(), routeReplacePatterns(), $routePattern);
}

function paramNames($pattern)
{
  preg_match_all(routeParamsPattern(), $pattern, $paramMatches, PREG_OFFSET_CAPTURE);

  return map(function ($item) {
    return $item[0];
  }, $paramMatches[1]);
}

function routeSegments($route)
{
  return explode('/', $route['pattern']);
}

function routeInternal($method, $pattern, callable $handler)
{
  return array_merge(compact('method', 'pattern', 'handler'), [
    'regex'   => createRegexFromPattern($pattern),
    'params'  => paramNames($pattern)
  ]);
}

function matchRouteAgainstPath($route, $requestPath)
{
  preg_match($route['regex'], $requestPath, $matches);
  return $matches;
}

function route(...$thisArgs)
{
  return call_user_func_array(curry('Concise\Routing\routeInternal'), $thisArgs);
}