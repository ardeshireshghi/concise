<?php

namespace Concise\Http\Adapter;

use const Concise\Http\Request\CONTENT_TYPES;

use function Concise\Http\Request\parseRouteParamsFromPath;
use function Concise\Http\Request\method;
use function Concise\Http\Request\isJson;
use function Concise\Http\Request\contentType;
use function Concise\Http\Request\headers;
use function Concise\FP\ifElse;
use function Concise\FP\compose;

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
    'params'  => is_null($matchingRoute) ? [] : parseRouteParamsFromPath($matchingRoute),
    'query'   => $_GET,
    'body'    => parseBody(),
    'method'  => method(),
    'headers' => headers()
  ];
}

function response(array $responseContext = [])
{
  return compose('Concise\Http\Adapter\_sendBody', 'Concise\Http\Adapter\_sendHeaders')($responseContext);
}

function _sendBody($responseContext)
{
  ob_start();
  echo $responseContext['body'];
  ob_end_flush();
  return $responseContext;
}

function _sendHeaders($responseContext)
{
  if (headers_sent()) {
    return $responseContext;
  }
  ob_start();

  $statusCode = (isset($responseContext['status'])) ? $responseContext['status'] : 200;

  http_response_code($statusCode);
  array_walk($responseContext['headers'], function ($value, $name) use ($responseContext, $statusCode) {
    header("$name: $value", false, $statusCode);
  });

  return $responseContext;
}
