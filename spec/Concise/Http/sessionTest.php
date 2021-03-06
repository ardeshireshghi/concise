<?php

use PHPUnit\Framework\TestCase;
use function Concise\Http\Session\middleware as sessionMiddleware;
use function Concise\Http\Session\set as setSession;

class SessionTest extends TestCase
{
  public function testSessionMiddleware()
  {
    $routeHandler = function ($request) {
      return array_merge($request, [ 'routehandler_sideeffect' => 'someresponse' ]);
    };

    $expectedRouteResponse = [
      'id' => 'somevalue',
      'routehandler_sideeffect' => 'someresponse'
    ];

    $middlewareConfig = [
      'session_name'  => 'super_server_fp_',
      'lifetime'      => 3600,
      'save_path'     => '/tmp'
    ];

    $handlerWithSessionMiddleware = sessionMiddleware($routeHandler)($middlewareConfig);

    $this->assertTrue(is_callable($handlerWithSessionMiddleware));
    $this->assertEquals($expectedRouteResponse, $handlerWithSessionMiddleware(['id' => 'somevalue']));
  }

  public function testSetSession()
  {
    $_SESSION = [
      'name' => 'somename'
    ];

    $newSession = setSession([
      'auth' => 'sometoken'
    ]);

    $this->assertEquals([
      'name'  => 'somename',
      'auth' => 'sometoken'
    ], $newSession);
  }
}
