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
  return curry('Concise\Routing\routeInternal')(...$thisArgs);
}

function post(...$thisArgs)
{
  return route('POST')(...$thisArgs);
}

function put(...$thisArgs)
{
  return route('PUT')(...$thisArgs);
}

function get(...$thisArgs)
{
  return route('GET')(...$thisArgs);
}

function delete(...$thisArgs)
{
  return route('DELETE')(...$thisArgs);
}

function head(...$thisArgs)
{
  return route('HEAD')(...$thisArgs);
}

function patch(...$thisArgs)
{
  return route('PATCH')(...$thisArgs);
}

function purge(...$thisArgs)
{
  return route('PURGE')(...$thisArgs);
}

function options(...$thisArgs)
{
  return route('OPTIONS')(...$thisArgs);
}

function trace(...$thisArgs)
{
  return route('TRACE')(...$thisArgs);
}

function connect(...$thisArgs)
{
  return route('CONNECT')(...$thisArgs);
}
