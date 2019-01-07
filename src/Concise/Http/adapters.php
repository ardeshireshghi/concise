<?php

namespace Concise\Http\Adapter;

use function Concise\Http\Request\parseRouteParamsFromPath;
use function Concise\Http\Request\method;

function parseBody()
{
  return $_POST;
}

function request(array $matchingRoute = null)
{
  return [
    'params' => is_null($matchingRoute) ? [] : parseRouteParamsFromPath($matchingRoute),
    'query'  => $_GET,
    'body'   => parseBody(),
    'method' => method()
  ];
}
