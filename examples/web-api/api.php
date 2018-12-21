<?php

require './vendor/autoload.php';

use function SuperServer\app;
use function SuperServer\Routing\route;

use function SuperServer\Http\Response\response;
use function SuperServer\Http\Session\set as setSession;
use function SuperServer\FP\ifElse;

app([
  route('GET', '/home', function() {
    header('Content-Type: application/json');
    response(json_encode([
      'route' => 'home',
      'message' => 'hello mate'
    ]));
  }),

  route('GET', '/api/user/:id/order', function($params) {
    header('Content-Type: application/json');
    response(json_encode([
      'route' => '/api/user',
      'data'  => [ 'user' => [ 'id' => $params['id'] ] ]
    ]));
  }),

  route('POST', '/api/upload', function() {
    header('Content-Type: application/json');
    response(json_encode([
      'route' => 'upload',
      'message' => 'Can upload your files'
    ]));
  }),

  route('GET', '/api/upload/:upload_id', function($params) {
    header('Content-Type: application/json');
    response(json_encode([
      'route' => 'GET upload with ID',
      'data'  => [ 'user' => [ 'upload_id' => $params['upload_id'] ] ]
    ]));
  }),

  route('GET', '/api/auth/:password', function($params) {
    header('Content-Type: application/json');
    return ifElse(function($password) {
      return $password === 'V3ryS3cur3Passw0rd';
    })(function() {
      return response(json_encode([
        'error' => false,
        'accesstoken'  => setSession([ 'accesstoken' => '12v6gh5y643fds453ghgdf4zmb7439kl'] )['accesstoken']
      ]));
    })(function() {
      return response(json_encode([
        'error'   => true,
        'message' => 'Invalid auth code'
      ]));
    })($params['password']);
  })
]);
