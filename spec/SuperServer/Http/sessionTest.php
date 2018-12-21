<?php

use PHPUnit\Framework\TestCase;
use function SuperServer\Http\Session\middleware as sessionMiddleware;
use function SuperServer\Http\Session\set as setSession;

class SessionTest extends TestCase
{
  public function testSessionMiddleware()
  {
    $routeHandler = function ($params) {
      return array_merge($params, [ 'routehandler_sideeffect' => 'someresponse' ]);
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

    $handlerWithSessionMiddleware = sessionMiddleware($middlewareConfig)($routeHandler);

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
