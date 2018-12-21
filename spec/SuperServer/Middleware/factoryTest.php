<?php

use PHPUnit\Framework\TestCase;
use function SuperServer\Middleware\Factory\create as createMiddleware;

class MiddlewareFactoryTest extends TestCase
{
  public function testCreateMiddleware()
  {
    $middlewareFn = function (array $middlewareParams = array(), $params) {
      return array_merge($params, [ 'middlewareName' => $middlewareParams['name'] ]);
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
    $handlerWithMiddleware = $middleware($middlewareParams)($routeHandler);

    $this->assertTrue(is_callable($handlerWithMiddleware));
    $this->assertEquals($expectedRouteResponse, $handlerWithMiddleware(['id' => 'somevalue']));
  }
}
