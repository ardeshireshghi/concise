<?php

require './vendor/autoload.php';

use function Concise\app;
use function Concise\Routing\route;

use function Concise\Http\Response\response;
use function Concise\Http\Response\setHeader;
use function Concise\Http\Response\send;
use function Concise\Http\Session\set as setSession;
use function Concise\FP\ifElse;

app([
  route('GET', '/home', function () {
    return send(setHeader('Content-Type', 'application/json')(response(json_encode([
      'route' => 'home',
      'message' => 'hello mate'
    ]), [])));
  }),

  route('GET', '/api/user/:id/order', function ($params) {
    return send(setHeader('Content-Type', 'application/json')(response(json_encode([
      'route' => '/api/user',
      'data'  => [ 'user' => [ 'id' => $params['id'] ] ]
    ]), [])));
  }),

  route('POST', '/api/upload', function () {
    return send(setHeader('Content-Type', 'application/json')(response(json_encode([
      'route' => 'upload',
      'message' => 'Can upload your files'
    ]), [])));
  }),

  route('GET', '/api/upload/:upload_id', function ($params) {
    return send(setHeader('Content-Type', 'application/json')(response(json_encode([
      'route' => 'GET upload with ID',
      'data'  => [ 'user' => [ 'upload_id' => $params['upload_id'] ] ]
    ]), [])));
  }),

  route('GET', '/api/auth/:password', function ($params) {
    return send(ifElse(function ($password) {
      return $password === 'V3ryS3cur3Passw0rd';
    })(function () {
      return setHeader('Content-Type', 'application/json')(response(json_encode([
        'route' => 'GET upload with ID',
        'error' => false,
        'accesstoken'  => setSession([ 'accesstoken' => '12v6gh5y643fds453ghgdf4zmb7439kl'])['accesstoken']
      ]), []));
    })(function () {
      return setHeader('Content-Type', 'application/json')(response(json_encode([
        'error'   => true,
        'message' => 'Invalid auth code'
      ]), []));
    })($params['password']));
  })
]);
