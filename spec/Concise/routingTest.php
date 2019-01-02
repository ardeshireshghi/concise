<?php

use PHPUnit\Framework\TestCase;
use function Concise\FP\map;
use function Concise\Routing\route;
use function Concise\Routing\post;
use function Concise\Routing\put;
use function Concise\Routing\get;
use function Concise\Routing\patch;
use function Concise\Routing\head;
use function Concise\Routing\options;
use function Concise\Routing\connect;
use function Concise\Routing\trace;
use function Concise\Routing\delete;
use function Concise\Routing\purge;

function partialExpectedRoute(callable $handlerFn)
{
  return [
    'pattern'  => '/api/user/:id/likes/:like_id',
    'handler'  => $handlerFn,
    'regex'   => '/\/api\/user\/(?<id>[\w\-]+)\/likes\/(?<like_id>[\w\-]+)/',
    'params'   => ['id', 'like_id']
  ];
}

class RoutingTest extends TestCase
{
  public function testRoute()
  {
    $handlerFn = function () {
    };
    $expected = [
      'method'   => 'GET',
      'pattern'  => '/api/user/:id',
      'handler'  => $handlerFn,
      'regex'   => '/\/api\/user\/(?<id>[\w\-]+)/',
      'params'   => ['id']
    ];

    $this->assertEquals($expected, route('GET')('/api/user/:id')($handlerFn));
  }

  public function testPostRoute()
  {
    $handlerFn = function () {
    };
    $expected = array_merge(partialExpectedRoute($handlerFn), [
      'method' => 'POST'
    ]);

    $this->assertEquals($expected, post('/api/user/:id/likes/:like_id')($handlerFn));
  }

  public function testPutRoute()
  {
    $handlerFn = function () {
    };
    $expected = array_merge(partialExpectedRoute($handlerFn), [
      'method' => 'PUT'
    ]);

    $this->assertEquals($expected, put('/api/user/:id/likes/:like_id')($handlerFn));
  }

  public function testGetRoute()
  {
    $handlerFn = function () {
    };
    $expected = array_merge(partialExpectedRoute($handlerFn), [
      'method' => 'GET'
    ]);

    $this->assertEquals($expected, get('/api/user/:id/likes/:like_id')($handlerFn));
  }

  public function testPatchRoute()
  {
    $handlerFn = function () {
    };
    $expected = array_merge(partialExpectedRoute($handlerFn), [
      'method' => 'PATCH'
    ]);

    $this->assertEquals($expected, patch('/api/user/:id/likes/:like_id')($handlerFn));
  }

  public function testHeadRoute()
  {
    $handlerFn = function () {
    };
    $expected = array_merge(partialExpectedRoute($handlerFn), [
      'method' => 'HEAD'
    ]);

    $this->assertEquals($expected, head('/api/user/:id/likes/:like_id')($handlerFn));
  }

  public function testDeleteRoute()
  {
    $handlerFn = function () {
    };
    $expected = array_merge(partialExpectedRoute($handlerFn), [
      'method' => 'DELETE'
    ]);

    $this->assertEquals($expected, delete('/api/user/:id/likes/:like_id')($handlerFn));
  }
  public function testPurgeRoute()
  {
    $handlerFn = function () {
    };
    $expected = array_merge(partialExpectedRoute($handlerFn), [
      'method' => 'PURGE'
    ]);

    $this->assertEquals($expected, purge('/api/user/:id/likes/:like_id')($handlerFn));
  }

  public function testOptionsRoute()
  {
    $handlerFn = function () {
    };
    $expected = array_merge(partialExpectedRoute($handlerFn), [
      'method' => 'OPTIONS'
    ]);

    $this->assertEquals($expected, options('/api/user/:id/likes/:like_id')($handlerFn));
  }

  public function testTraceRoute()
  {
    $handlerFn = function () {
    };
    $expected = array_merge(partialExpectedRoute($handlerFn), [
      'method' => 'TRACE'
    ]);

    $this->assertEquals($expected, trace('/api/user/:id/likes/:like_id')($handlerFn));
  }

  public function testConnectRoute()
  {
    $handlerFn = function () {
    };
    $expected = array_merge(partialExpectedRoute($handlerFn), [
      'method' => 'CONNECT'
    ]);

    $this->assertEquals($expected, connect('/api/user/:id/likes/:like_id')($handlerFn));
  }
}
