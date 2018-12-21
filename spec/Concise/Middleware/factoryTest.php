<?php

use PHPUnit\Framework\TestCase;
use function Concise\Middleware\Factory\create as createMiddleware;

class MiddlewareFactoryTest extends TestCase
{
  public function testCreateMiddleware()
  {
    $middlewareFn = function (callable $handler, array $middlewareParams = array(), array $reqParams = array()) {
      return $handler(array_merge($reqParams, [ 'middlewareName' => $middlewareParams['name'] ]));
    };

    $routeHandler = function ($params) {
      return array_merge($params, [ 'response' => 'someresponse' ]);
    };

    $middlewareParams = [
      'name' => 'testmiddleware'
    ];

    $expectedRouteResponse = [
      'id' => 'somevalue',
      'middlewareName' => 'testmiddleware',
      'response' => 'someresponse'
    ];

    $middleware = createMiddleware($middlewareFn);
    $handlerWithMiddleware = $middleware($routeHandler)($middlewareParams);

    $this->assertTrue(is_callable($handlerWithMiddleware));
    $this->assertEquals($expectedRouteResponse, $handlerWithMiddleware(['id' => 'somevalue']));
  }
}
