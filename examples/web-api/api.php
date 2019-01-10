<?php

require './vendor/autoload.php';

use function Concise\app;
use function Concise\Routing\route;
use function Concise\Routing\post;

use function Concise\Http\Response\response;
use function Concise\Http\Request\path as requestPath;
use function Concise\Http\Response\setHeader;
use function Concise\Http\Response\statusCode;
use function Concise\Http\Session\set as setSession;
use function Concise\FP\ifElse;
use function Concise\FP\tryCatch;

use function Concise\Middleware\Factory\create as createMiddleware;

function validTokens()
{
  return [
    'Bearer abcd1234efgh5678',
    'Bearer 1234abcd5678efgh'
  ];
}

function authorizer($nextRouteHandler)
{
  return tryCatch(function ($request) use ($nextRouteHandler) {
    if (!isset($_SERVER['HTTP_AUTHORIZATION']) || !in_array($_SERVER['HTTP_AUTHORIZATION'], validTokens())) {
      throw new \Exception('Invalid access token');
    }
    return $nextRouteHandler($request);
  }, function (\Exception $error) {
    return setHeader('Content-Type', 'application/json')(response(json_encode([
      'error' => true,
      'status' => 401,
      'message' => $error->getMessage()
    ]))(statusCode(401, [])));
  });
}

function routeProtected($requestPath)
{
  return substr($requestPath, 0, 4) === '/api' && strpos($requestPath, 'login') === false;
}

$authMiddleware = createMiddleware(function (callable $nextRouteHandler, array $middlewareParams = [], array $request = []) {
  return ifElse('routeProtected', function () use ($nextRouteHandler, $request) {
    return authorizer($nextRouteHandler)($request);
  }, function () use ($nextRouteHandler, $request) {
    return $nextRouteHandler($request);
  })(requestPath());
});

app([
  route('GET', '/home', function () {
    return setHeader('Content-Type', 'application/json')(response(json_encode([
      'route' => 'home',
      'message' => 'hello mate'
    ]), []));
  }),

  route('GET', '/api/user/:id', function (array $request) {
    return setHeader('Content-Type', 'application/json')(response(json_encode([
      'route' => '/api/user',
      'data'  => [ 'user' => [ 'id' => $request['params']['id'] ] ]
    ]), []));
  }),

  route('POST', '/api/upload', function (array $request) {
    return setHeader('Content-Type', 'application/json')(response(json_encode([
      'route'   => 'upload',
      'data'    => [
        'filename' => isset($request['body']['filename']) ? $request['body']['filename'] : ''
      ],
      'message' => 'Can upload your files'
    ]), []));
  }),

  route('GET', '/api/upload/:upload_id', function (array $request) {
    return setHeader('Content-Type', 'application/json')(response(json_encode([
      'route' => 'GET upload with ID',
      'data'  => [ 'user' => [ 'upload_id' => $request['params']['upload_id'] ] ]
    ]), []));
  }),

  post('/api/auth/login')(function (array $request) {
    return ifElse(function ($body) {
      return isset($body['password']) && $body['password'] === 'V3ryS3cur3Passw0rd';
    })(function () {
      return setHeader('Content-Type', 'application/json')(response(json_encode([
        'error' => false,
        'access_token'  => setSession([ 'access_token' => '12v6gh5y643fds453ghgdf4zmb7439kl'])['access_token']
      ]), []));
    })(function () {
      return statusCode(422)(setHeader('Content-Type', 'application/json')(response(json_encode([
        'error'   => true,
        'message' => 'Invalid password'
      ]), [])));
    })($request['body']);
  })
])([
  $authMiddleware
]);
