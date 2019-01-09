<?php

namespace Concise\Http\Adapter;

use const Concise\Http\Request\CONTENT_TYPES;

use function Concise\Http\Request\parseRouteParamsFromPath;
use function Concise\Http\Request\method;
use function Concise\Http\Request\isJson;
use function Concise\Http\Request\contentType;
use function Concise\FP\ifElse;

function parseBody()
{
  return ifElse(function ($contentType) {
    return $contentType === null ||
    in_array($contentType, [ CONTENT_TYPES['multipart'] , CONTENT_TYPES['urlencoded']]);
  }, function () {
    return $_POST;
  }, function () {
    $input = file_get_contents('php://input');
    return (contentType() === CONTENT_TYPES['json'] || isJson($input))
    ? json_decode($input, true)
    : $input;
  })(contentType());
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
