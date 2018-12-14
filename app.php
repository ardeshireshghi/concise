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

$server->error(function() {
  echo 'Route can not be found';
});

$server->start();
