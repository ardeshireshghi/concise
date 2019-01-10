<?php

namespace Concise\Http\Response;

use function Concise\FP\partial;
use function Concise\FP\curry;
use function Concise\FP\compose;

function response(...$thisArgs)
{
  return call_user_func_array(curry(_createResponse()), $thisArgs);
}

function setHeader(...$thisArgs)
{
  return call_user_func_array(curry(_createSetHeader()), $thisArgs);
}

function statusCode(...$thisArgs)
{
  return call_user_func_array(curry(_createStatusCode()), $thisArgs);
}

function _createStatusCode()
{
  return function (int $code, array $responseContext = []) {
    $responseContext = (count($responseContext) === 0) ? defaultResponseContext() : $responseContext;
    return array_replace_recursive([], $responseContext, [
      'status' => $code
    ]);
  };
}

function defaultResponseContext()
{
  return [
    'headers' => [
      'Content-Type' => 'text/html'
    ],
    'body' => ''
  ];
}

function _createSetHeader()
{
  return function ($headerName, $headerValue, array $responseContext = []) {
    $responseContext = (count($responseContext) === 0) ? defaultResponseContext() : $responseContext;
    return array_replace_recursive([], $responseContext, [
      'headers' => [
        $headerName => $headerValue
      ]
    ]);
  };
}

function _createResponse()
{
  return function ($data, array $responseContext = []) {
    $responseContext = (count($responseContext) === 0) ? defaultResponseContext() : $responseContext;
    return array_merge($responseContext, [
      'body' => $data
    ]);
  };
}
