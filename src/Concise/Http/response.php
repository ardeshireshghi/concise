<?php

namespace Concise\Http\Response;

use function Concise\FP\partial;
use function Concise\FP\curry;
use function Concise\FP\compose;

function response(...$thisArgs)
{
  return curry('Concise\Http\Response\_response')(...$thisArgs);
}

function json($responseBodyArray, ...$thisArgs)
{
  return curry(compose(
    setHeader('Content-Type', 'application/json'),
    compose('Concise\Http\Response\response', 'json_encode')($responseBodyArray)
  ))(...$thisArgs);
}

function setHeader(...$thisArgs)
{
  return curry('Concise\Http\Response\_setHeader')(...$thisArgs);
}

function statusCode(...$thisArgs)
{
  return curry('Concise\Http\Response\_statusCode')(...$thisArgs);
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

function _statusCode(int $code, array $responseContext = [])
{
  $responseContext = (count($responseContext) === 0) ? defaultResponseContext() : $responseContext;
  return array_replace_recursive([], $responseContext, [
    'status' => $code
  ]);
}

function _setHeader($headerName, $headerValue, array $responseContext = []) {
  $responseContext = (count($responseContext) === 0) ? defaultResponseContext() : $responseContext;
  return array_replace_recursive([], $responseContext, [
    'headers' => [
      $headerName => $headerValue
    ]
  ]);
}

function _response($data, array $responseContext = [])
{
  $responseContext = (count($responseContext) === 0) ? defaultResponseContext() : $responseContext;
  return array_merge($responseContext, [
    'body' => $data
  ]);
}
