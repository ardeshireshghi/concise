<?php

require './vendor/autoload.php';

use SuperServer\Server;

$server = Server::create();

$server->get('/home', function() {
  header('Content-Type: application/json');

  echo json_encode([
    'route' => 'home',
    'message' => 'hello mate'
  ]);
});

$server->post('/api/upload', function() {
  header('Content-Type: application/json');

  echo json_encode([
    'route' => 'upload',
    'message' => 'Can upload your files'
  ]);
});

$server->error(function() {
  echo 'Route can not be found';
});

$server->start();
