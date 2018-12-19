<?php

require './vendor/autoload.php';

use function SuperServer\app;
use function SuperServer\Routing\route;
use function SuperServer\Http\Response\response;

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
  })
]);
